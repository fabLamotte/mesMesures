<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

use App\Entity\Mesures;
use App\Entity\InscriptionMesure;
use DateTime;


class MesureController extends AbstractController
{

    /**
     * @Route("/mesure", name="mesure")
     */
    public function mesure(): Response
    {
        $em = $this->getDoctrine()->getManager();
        $mesures = $em->getRepository(Mesures::class)->findAll();
        return $this->render('mesure/mesure.html.twig', [
            'mesures' => $mesures,
        ]);
    }

    /**
     * @Route("ajout_mesure_ajax",name="ajout_mesure_ajax",options={"expose"=true})
     */
    public function ajoutMesureAjax(Request $request){  
        // récupération des variables
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $dateDuJour = new DateTime();
        $bras = $request->request->get('bras');
        $ventre = $request->request->get('ventre');
        $hanches = $request->request->get('hanches');
        $cuisses = $request->request->get('cuisses');
        $genou = $request->request->get('genou');
        $errors = 0;
        $regex = '/^[0-9.]{1,8}$/';

        $tab = [["name" => 'Bras',"value" => $bras],["name" => 'Ventre',"value" => $ventre],["name" => 'Hanches',"value" => $hanches],["name" => 'Cuisses',"value" => $cuisses],["name" => 'Genou',"value" => $genou],];

        foreach($tab as $t){
            if(preg_match($regex, $t['value']) && $t['value'] != null){
                $inscription_mesure = new InscriptionMesure();
                $mesure = $em->getRepository(Mesures::class)->findOneby(['name' => $t['name']]);
                $inscription_mesure->setDate($dateDuJour);
                $inscription_mesure->setCm($t['value']);
                $inscription_mesure->setMesures($mesure);
                $inscription_mesure->setUser($user);
                $em->persist($inscription_mesure);
            } else {
                $errors ++;
            }
        }

        if($errors === 0){
            // Enregistrement et retour ajax
            $em->flush();
            return $this->json('good');
        } else {
            // On enregistre pas les informations et on retourne les erreurs
            return $this->json('error');
        }
    }
}
