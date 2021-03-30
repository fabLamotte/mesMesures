<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

use App\Entity\InscriptionMesure;
use App\Entity\Mesures;
use App\Entity\ProfilSportif;
use App\Entity\Sexe;
use App\Services\UploadImageService;

/** //////////// METHODE ///////////
 *    profil
 * 
 * ///////////// AJAX /////////////
 *    reload_chart
 *    add_photo_profil
 */
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

        // Recherche d'informations manquantes
            $mesurePoids = $em->getRepository(Mesures::class)->findOneBy(['name' => 'Poids']);
            $mesureTaille = $em->getRepository(Mesures::class)->findOneBy(['name' => 'Taille corps']);
            $poids = $em->getRepository(InscriptionMesure::class)->findLastDataByUserAndMesure($user, $mesurePoids);
            $taille = $em->getRepository(InscriptionMesure::class)->findLastDataByUserAndMesure($user, $mesureTaille);
            $sexe = $user->getSexe();
            $age = $user->getAge();
            $weightGoal = $user->getWeightGoal();
            $profilSportif = $user->getProfilSportif();
            $profilSportifName = $em->getRepository(ProfilSportif::class)->findAll();
            if($age === null || $age === ''
                || $sexe === null || $sexe === ''
                || $profilSportif === null || $profilSportif === ''
                || $poids === null || $poids->getCm() === ''  
                || $taille === null || $taille->getCm() == ''){
                $besoinCalorique = null;
            } else {
                $besoinCalorique = round($this->calculBesoinCaloriqueBase($age, $sexe,$profilSportif, $poids->getCm(), $taille->getCm()),2);
            }

            $data = [$poids, $taille, $sexe, $age, $weightGoal, $profilSportif];
        
        return $this->render('profil/index.html.twig', [
            'user'              => $user,
            'data'              => $data,
            'profilsSportif'    => $profilSportifName,
            'besoinCalorique'   => $besoinCalorique
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

    /**
     * @Route("add_photo_profil",name="add_photo_profil",options={"expose"=true})
     */
    public function addPhotoProfil(Request $request, UploadImageService $upload){
        $response = $upload->verifyUploadedFile(
            $_FILES, 
            $this->getUser(), 
            $this->getDoctrine()->getManager(), 
            $this->getParameter('directory')
        );
        return $this->json($response);
    }

    /**
     * @Route("add_missed_info",name="add_missed_info",options={"expose"=true})
     */
    public function addMissedInfo(Request $request){
        // Connexion BDD
            $em = $this->getDoctrine()->getManager();
        // Récupération variable
            $poids = $request->request->get('poids');
            $taille = $request->request->get('taille');
            $age = $request->request->get('age');
            $sexe = $request->request->get('sexe');
            $profilSportif = $request->request->get('profilSportif');

        // Autres données
            $user = $this->getUser();
            $date = new \DateTime();

        // Recherche en BDD
            $poidsMesure = $em->getRepository(Mesures::class)->findOneBy(['name'=>'Poids']);
            $tailleMesure = $em->getRepository(Mesures::class)->findOneBy(['name'=>'Taille corps']);
            $recherche_inscription_poids = $em->getRepository(InscriptionMesure::class)->findOneBy(['user'=>$user, 'mesures'=>$poidsMesure]);
            $recherche_inscription_taille = $em->getRepository(InscriptionMesure::class)->findOneBy(['user'=>$user, 'mesures'=>$tailleMesure]);
            $genreSexe = $em->getRepository(Sexe::class)->findOneBy(['libelle'=>$sexe]);
            $profil_sportif = $em->getRepository(ProfilSportif::class)->findOneBy(['libelle'=>$profilSportif]);

        // Regex de vérification de nombre à virgule ou non => pour le poids, taille, et age
            $regex = '/^[\d]+[\.|\,]{0,1}[\d]{0,2}$/';
            $regexAge = '/^[\d]+$/';
        // Vérification Age
            if($user->getAge() === '' || $user->getAge() === null){
                if($age === '' || $age === null){
                    return $this->json(['state'=>'erreur', 'message' => 'Age non renseigné', 'input' => '.ageInput']);
                } else {
                    if(preg_match($regexAge, $age)){
                        $user->setAge($age);
                    } else {
                        return $this->json(['state'=>'erreur', 'message' => 'Erreur dans l\'inscription de l\'âge.', 'input' => '.ageInput']);
                    }
                }
            }

        // Vérification Poids 
            if($recherche_inscription_poids === null){                      // si aucune inscription en base de données, on regarde la variable récupérée
                if($poids === '' || $poids === null ){                      // Si la variable récupérée est null => erreur, donnée non renseignée
                    return $this->json(['state'=>'erreur', 'message' => 'Poids non renseigné.', 'input' => '.poidsInput']);
                } else {
                    if(preg_match($regex, $poids)){    // Vérification de la validité de la valeur renseignée
                        $nouvelle_inscription_mesure = new InscriptionMesure();
                        $nouvelle_inscription_mesure->setDate($date);
                        $nouvelle_inscription_mesure->setCm($poids);
                        $nouvelle_inscription_mesure->setMesures($poidsMesure);
                        $nouvelle_inscription_mesure->setUser($user);
                        $em->persist($nouvelle_inscription_mesure);
                    } else {
                        return $this->json(['state'=>'erreur', 'message' => 'Erreur dans l\'inscription du poids.', 'input' => '.poidsInput']);
                    }
                }
            }

        // Vérification Taille
            if($recherche_inscription_taille === '' || $recherche_inscription_taille === null){ // si aucune inscription en base de données, on regarde la variable récupérée
                if($taille === '' || $taille === null){                   // Si la variable récupérée est null => erreur, donnée non renseignée
                    return $this->json(['state'=>'erreur', 'message' => 'Taille non renseigné.', 'input' => '.tailleInput']);
                } else {
                    if(preg_match($regex, $taille)){    // Vérification de la validité de la valeur renseignée
                        $nouvelle_inscription_mesure = new InscriptionMesure();
                        $nouvelle_inscription_mesure->setDate($date);
                        $nouvelle_inscription_mesure->setCm($taille);
                        $nouvelle_inscription_mesure->setMesures($tailleMesure);
                        $nouvelle_inscription_mesure->setUser($user);
                        $em->persist($nouvelle_inscription_mesure);
                    } else {
                        return $this->json(['state'=>'erreur', 'message' => 'Erreur dans l\'inscription de la taille.', 'input' => '.tailleInput']);
                    }
                }
            }

        

        // Vérification du sexe
            ($user->getSexe() === null)? $user->setSexe($genreSexe): '';

        // Vérification profilSportif
            ($user->getProfilSportif() === null)? $user->setProfilSportif($profil_sportif): '';

        // Vérification finale avant enregistrement
            if(($recherche_inscription_poids !== null || $poids !== null) && ($recherche_inscription_taille !== null || $taille !== null) && $user->getAge() !== null && $user->getSexe() !== null && $user->getProfilSportif() !== null){
                $selectPoids = ($recherche_inscription_poids === null)? $poids : $recherche_inscription_poids->getCm();
                $selectTaille = ($recherche_inscription_taille === null)? $taille : $recherche_inscription_taille->getCm();
                $besoinCalorique = round($this->calculBesoinCaloriqueBase($user->getAge(), $user->getSexe(), $user->getProfilSportif(), $selectPoids, $selectTaille),2);
                if($besoinCalorique === 0 || $besoinCalorique === null){
                    $this->json(['state' => 'erreur', 'message' => 'Erreur dans le calcul des besoins calorique journalier.', 'input' => '.target_error_message']);
                } else {
                    $em->flush();
                    $em->clear();
                    return $this->json(['success' => 'Enregistrement effectué avec succès', 'besoinCalorique'=>$besoinCalorique]);
                }
            } else {
                return $this->json(['erreur' => 'Une erreur est survenue, veuillez réessayer ultérieurement.']);
            }
    }

    function calculBesoinCaloriqueBase($age, $genreSexe, $profil_sportif, $poids, $taille){
        $resultat = 0;
        if($genreSexe->getLibelle() === 'Homme'){
            $resultat = (66.5 + (17.75 * $poids) + (5 * $taille) - (6.77 * $age)) * $profil_sportif->getValue();
        } else if ($genreSexe->getLibelle() === 'Femme'){
            $resultat = (655 + (9.56 * $poids) + (1.85 * $taille) - (4.67 * $age)) * $profil_sportif->getValue();
        }
        return $resultat;
    }
}
