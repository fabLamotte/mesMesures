<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

use App\Entity\Mesures;
use App\Entity\InscriptionMesure;
use \DateTime;


class MesureController extends AbstractController
{

    /**
     * @Route("/mesure", name="mesure")
     */
    public function mesure(): Response
    {
        // Vérification d'une connexion d'un user
            if($this->getUser() == null){
                return $this->redirectToRoute("");
            } 
            
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $mesures = $em->getRepository(Mesures::class)->findAll(); // Recherche de toutes les zones cibles

        $mesures_a_inscrire = [];

        // On boucle sur tous les types de mesures
            foreach($mesures as $mesure){
                // Pour chaque mesure, on voit si une donnée plus recente existe
                    $inscription = $em->getRepository(InscriptionMesure::class)->findLastDataByUserAndMesure($this->getUser(), $mesure);

                    // Si une donnée récente existe...
                    if($inscription){
                        // On vérifie qu'elle date d'il y a plus d'une semaine
                        $timestamp = strtotime($inscription->getDate()->format('Y-m-d'));       // Nombre de seconde écoulée jusqu'à la prise de la mesure
                        $semaine = 60*60*24*7;                                                  // Nombre de seconde que contient une semaine complète
                        $date_du_jour = new \DateTime();                                        // Date d'aujourd'hui
                        $timestamp_du_jour = strtotime($date_du_jour->format('Y-m-d'));         // Nombre de seconde écoulée jusqu'à aujourd'hui

                        // On garde la mesure à inscrire si la derniere prise remonte à il y a une semaine
                        if(($timestamp_du_jour - $semaine) > $timestamp){
                            $mesures_a_inscrire[] = $mesure;
                        }
                    } else {
                        $mesures_a_inscrire[] = $mesure;
                    }
            }

        return $this->render('mesure/mesure.html.twig', [
            'mesures'   => $mesures_a_inscrire,
            'user'      => $user,
        ]);
    }

    /**
     * @Route("/nouvelle_mesure", name="nouvelle_mesure", options={"expose"=true})
     */
    public function nouvelle_mesure(Request $request){
        // Récupération des variables
            $user = $this->getUser();
            $em = $this->getDoctrine()->getManager();
            $value = $request->request->get('value');
            $id = $request->request->get('id');
            $date = new \DateTime();

        // Vérification qu'il s'agisse bien d'un integer / float
            $regex = '/^[\d]+[\.|\,]{0,1}[\d]{0,2}$/';
            if(preg_match($regex, $value)){
                // Remplacement s'il y a une virgule
                    $eclatement = explode(',', $value);
                    (isset($eclatement[1]))? $value = $eclatement[0].'.'.$eclatement[1] : $value = $eclatement[0];

                // Rentrée des données
                    $inscriptionMesure = new InscriptionMesure();
                    $inscriptionMesure->setDate($date);
                    $inscriptionMesure->setCm($value);
                    $mesure = $em->getRepository(Mesures::class)->findOneBy(['id' => $id]);
                    $inscriptionMesure->setMesures($mesure);
                    $inscriptionMesure->setUser($user);
                    $em->persist($inscriptionMesure);
                    $em->flush();

                // Retour
                    return $this->json(['success', 'Inscription réalisée avec succès']);
            } else {
                $message = 'La valeur de votre taille est incorrecte.';
                return $this->json(['error', $message]);
            }
    }
}
