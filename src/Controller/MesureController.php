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

        $data = $em->getRepository(InscriptionMesure::class)->findLastDataByUser($this->getUser());
        $lastTime = 0;
        $wait = [];

        foreach($data as $last){
            $timestamp = strtotime($last->getDate()->format('Y-m-d'));
            if($timestamp >= $lastTime){
                $lastTime = $timestamp;                     //  Timestamp de la derniere inscription
                $une_semaine = 60*60*24*7;                  //  Nombre de secondes que fait une semaine
                $timestamp_du_jour = strtotime('now');      //  Timestamp aujourd'hui
                if(($timestamp + $une_semaine) > $timestamp_du_jour){   // S'il ne s'est pas écoulé plus d'une semaine depuis la dernière inscription
                    $wait[] = $last->getMesures()->getName();           // On ajoute la zone du corps au tableau des zone indisponible
                }
            }
        }

        return $this->render('mesure/mesure.html.twig', [
            'mesures'   => $mesures,
            'user'      => $user,
            'wait'      => $wait,
            'nbMesure'  => count($wait),
        ]);
    }

    /**
     * @Route("/nouvelle_mesure", name="nouvelle_mesure", options={"expose"=true})
     */
    public function nouvelle_mesure(Request $request){
        $mesures = $request->request->get('array');
        $em = $this->getDoctrine()->getManager();
        $date = new \DateTime();
        $confirmed = [];
        $errors = 0;
        
        foreach($mesures as $mesure => $data){
            if(isset($data[1])){
                if($data[1] != ""){
                    $new_donnees = new InscriptionMesure();
                    $mesur = $em->getRepository(Mesures::class)->findOneBy(['name' => $data[0]]);
                    $new_donnees->setMesures($mesur);
                    $new_donnees->setCm(intval($data[1]));
                    $new_donnees->setUser($this->getUser());
                    $new_donnees->setDate($date);
                    $em->persist($new_donnees);
                    $confirmed[] = $data[0];
                } else {
                    $errors++;
                }
            } 
        }
        $em->flush();

        return $this->json([$confirmed, $errors]);
    }
}
