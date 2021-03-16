<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

use App\Entity\InscriptionMesure;
use App\Entity\Mesures;


class ProfilController extends AbstractController
{
    /**
     * @Route("/profil", name="profil")
     */
    public function index(): Response
    {
        // Vérification d'une connexion d'un user
            if($this->getUser() == null){
                return $this->redirectToRoute("");
            } 

        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $cibles = $em->getRepository(InscriptionMesure::class)->findBy(['user' => $user]);


        
        return $this->render('profil/index.html.twig', [
            'user'     => $user
        ]);
    }

    /**
     * @Route("reload_chart",name="reload_chart",options={"expose"=true})
     */
    public function reloadChart(Request $request){
        $em = $this->getDoctrine()->getManager();
        $param = $request->request->get('cible');
        $cible = $em->getRepository(Mesures::class)->findOneBy(['name' => $param]);
        $mesures = $em->getRepository(InscriptionMesure::class)->researchByCible($cible);

        $labels = [];
        $data = [];

        foreach($mesures as $mesure){   
            array_push($labels, $mesure->getDate()->format('d-m-Y'));
            array_push($data, $mesure->getCm());
        }

        $response = [$labels, $data];

        return $this->json($response);
    }
}
