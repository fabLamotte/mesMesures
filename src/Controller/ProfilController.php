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

        // Connexion bdd
            $em = $this->getDoctrine()->getManager();
            $user = $this->getUser();
            $mesurePoids = $em->getRepository(Mesures::class)->findOneBy(['name'=>'Poids']);
            $mesureTaille = $em->getRepository(Mesures::class)->findOneBy(['name'=>'Taille corps']);
            $premiere_mesure_poids = $em->getRepository(InscriptionMesure::class)->findFirstDataByUserAndMesure($user, $mesurePoids);
            $derniere_mesure_poids = $em->getRepository(InscriptionMesure::class)->findLastDataByUserAndMesure($user, $mesurePoids);
            $derniere_mesure_taille = $em->getRepository(InscriptionMesure::class)->findLastDataByUserAndMesure($user, $mesureTaille);
            $profilsportif = $em->getRepository(ProfilSportif::class)->findAll();
            $datas = [];
            $dataForgots = [];

        // Stockage des données manquantes à remplir
            ($user->getWeightGoal() === null || $user->getWeightGoal() === '')? $dataForgots[] = 'ObjectifPoids' : '';      // Objectif Poids
            ($derniere_mesure_poids === null || $derniere_mesure_poids === '')? $dataForgots[] = 'Poids' : '';              // Poids
            ($derniere_mesure_taille === null || $derniere_mesure_taille === '')? $dataForgots[] = 'Taille' : '';           // Objectif taille
            ($user->getAge() === null || $user->getAge() === '')? $dataForgots[] = 'Age' : '';                              // Age
            ($user->getSexe() === null || $user->getSexe() === '')? $dataForgots[] = 'Sexe' : '';                           // Sexe
            ($user->getProfilSportif() === null || $user->getProfilSportif() === '')? $dataForgots[] = 'ProfilSportif' : '';// profilSportif

        // Recherche objectif poids  
            ($user->getWeightGoal() !== null)? $datas['weight_goal'] = $user->getWeightGoal() : $datas['weight_goal'] = null;    
            ($premiere_mesure_poids !== null)? $datas['weight_first'] = $premiere_mesure_poids->getCm() : $datas['weight_first'] = null;
            ($derniere_mesure_poids !== null)? $datas['weight_actual'] = $derniere_mesure_poids->getCm() : $datas['weight_actual'] = null;
            if($premiere_mesure_poids !== null && $derniere_mesure_poids !== null){
                $datas['weight_coefficient'] = $this->calculCoefWeight($user->getWeightGoal(), $premiere_mesure_poids->getCm(), $derniere_mesure_poids->getCm());
            } else {
                $datas['weight_coefficient'] = null;
            }
            
        // Recherche nombre de mesures restantes de la semaine
            $datas['nombre_mesures_restantes'] = $this->mesuresRestantes();

        // Recherche besoins caloriques de base
            if($user->getAge() === null || $user->getAge() === '' || $user->getSexe() === null || $user->getSexe() === '' || $user->getProfilSportif() === null || $user->getProfilSportif() === '' || $derniere_mesure_poids === null || $derniere_mesure_poids === "" || $derniere_mesure_taille === null || $derniere_mesure_taille === ""){
                $datas['besoin_calorique_de_base'] = null;
            } else {
                $datas['besoin_calorique_de_base'] = $this->calculBesoinCaloriqueBase($user->getAge(), $user->getSexe()->getLibelle(), $user->getProfilSportif()->getValue(), $derniere_mesure_poids->getCm(), $derniere_mesure_taille->getCm());
            }

        // Recherche IMC
            if($derniere_mesure_poids !== null && $derniere_mesure_taille !== null){
                $datas['imc'] = $this->imc($derniere_mesure_taille->getCm(), $derniere_mesure_poids->getCm());
            } else {
                $datas['imc'] = null;
            }
        return $this->render('profil/index.html.twig', [
            'user'              => $user,
            'datas'             => $datas,
            'dataForgot'        => $dataForgots,
            'profilsSportif'    => $profilsportif,
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
        // Connexion bdd et recup user
            $manager = $this->getDoctrine()->getManager();
            $user = $this->getUser();
            $date = new \DateTime();

        // Récupération des variables
            $age = $request->request->get('age');
            $poids = $request->request->get('poids');
            $objectif_poids = $request->request->get('objectif_poids');
            $taille = $request->request->get('taille');
            $sexe = $request->request->get('sexe');
            $profil_sportif = $request->request->get('profil_sportif');
            $regexPoids = "/^[\d]+[\.|\,]{0,1}[\d]{0,2}$/";
            $regexAge = "/^[\d]{0,2}$/";

        /**
         * Traitement des données
         */
            // Age
                if($user->getAge() === null){
                    if($age !== null && $age !== ''){
                        if(preg_match($regexAge, $age)){
                            $user->setAge($age);
                        } else {
                            return $this->json(['state'=>'erreur', 'message'=> 'Valeur du champs incorrecte', 'input' => '.ageInput']);
                        }
                    } else {
                        return $this->json(['state'=>'erreur', 'message'=> 'Votre champs est vide', 'input' => '.ageInput']);
                    }
                }

            // Poids 
                $mesurePoids = $manager->getRepository(Mesures::class)->findOneBy(['name'=>'Poids']);
                $recherche_inscription_poids = $manager->getRepository(InscriptionMesure::class)->findLastDataByUserAndMesure($user,$mesurePoids);
                if($recherche_inscription_poids === null){
                    if($poids !== null && $poids !== ''){
                        if(preg_match($regexPoids, $poids)){
                            $nouvelle_inscription_mesure = new InscriptionMesure();
                            $nouvelle_inscription_mesure->setDate($date);
                            $nouvelle_inscription_mesure->setCm($poids);
                            $nouvelle_inscription_mesure->setMesures($mesurePoids);
                            $nouvelle_inscription_mesure->setUser($user);
                            $manager->persist($nouvelle_inscription_mesure);
                        } else {
                            return $this->json(['state'=>'erreur', 'message'=> 'Valeur du champs incorrecte', 'input' => '.poidsInput']);
                        }
                    } else {
                        return $this->json(['state'=>'erreur', 'message'=> 'Votre champs est vide', 'input' => '.poidsInput']);
                    }
                }
                

            // objectif_poids
                if($user->getWeightGoal() === null){
                    if($objectif_poids !== null || $objectif_poids !== ''){
                        if(preg_match($regexPoids, $objectif_poids)){
                            $user->setWeightGoal($objectif_poids);
                        } else {
                            return $this->json(['state'=>'erreur', 'message'=> 'Valeur du champs incorrecte', 'input' => '.objectifPoidsInput']);
                        }
                    } else {
                        return $this->json(['state'=>'erreur', 'message'=> 'Votre champs est vide', 'input' => '.objectifPoidsInput']);
                    }
                }

            // taille
                $mesureTaille = $manager->getRepository(Mesures::class)->findOneBy(['name'=>'Taille corps']);
                $recherche_inscription_taille = $manager->getRepository(InscriptionMesure::class)->findLastDataByUserAndMesure($user,$mesureTaille);
                if($recherche_inscription_taille === null){
                    if($taille !== null && $taille !== ''){
                        if(preg_match($regexPoids, $taille)){
                            $nouvelle_inscription_mesure = new InscriptionMesure();
                            $nouvelle_inscription_mesure->setDate($date);
                            $nouvelle_inscription_mesure->setCm($taille);
                            $nouvelle_inscription_mesure->setMesures($mesureTaille);
                            $nouvelle_inscription_mesure->setUser($user);
                            $manager->persist($nouvelle_inscription_mesure);
                        } else {
                            return $this->json(['state'=>'erreur', 'message'=> 'Valeur du champs incorrecte', 'input' => '.tailleInput']);
                        }
                    } else {
                        return $this->json(['state'=>'erreur', 'message'=> 'Votre champs est vide', 'input' => '.tailleInput']);
                    }
                }

            // Sexe 
                if($user->getSexe() === null){
                    if($sexe !== null && $sexe !== ""){
                        $typeSexe = $manager->getRepository(Sexe::class)->findOneBy(['libelle'=>$sexe]);
                        $user->setSexe($typeSexe);
                    } else {
                        return $this->json(['state'=>'erreur', 'message'=> 'Veuillez choisir entre homme ou femme', 'input' => '.fake-input']);
                    }
                }

            // Profil Sportif
                if($user->getProfilSportif() === null){
                    if($profil_sportif === null || $profil_sportif === ''){
                        return $this->json(['state'=>'erreur', 'message'=> 'Veuillez choisir parmis les profils sportifs', 'input' => '.target-error-message']);
                    } else {
                        $typeProfilSportif = $manager->getRepository(ProfilSportif::class)->findOneBy(['libelle'=>$profil_sportif]);
                        $user->setProfilSportif($typeProfilSportif);
                    }
                }

        /**
         *  Validation 
         */
            $manager->flush();
            $manager->clear();
            return $this->json(['state'=>'success', 'message'=> 'Données enregistrées correctement', 'input' => '.modal-footer']);
    }

    /**
     * @Route("reload_info",name="reload_info",options={"expose"=true})
     */
    public function reloadInfo(Request $request){
        // Connexion et récupération user
            $em = $this->getDoctrine()->getManager();
            $user = $this->getUser();

        // Recherche des dernieres mesures en taille et poids
            $mesurePoids = $em->getRepository(Mesures::class)->findOneBy(['name'=>'Poids']);
            $mesureTaille = $em->getRepository(Mesures::class)->findOneBy(['name'=>'Taille corps']);
            $poids = $em->getRepository(InscriptionMesure::class)->findLastDataByUserAndMesure($user, $mesurePoids); // Derniere inscription poids
            $taille = $em->getRepository(InscriptionMesure::class)->findLastDataByUserAndMesure($user, $mesureTaille); // Derniere inscription taille
    
        // Recherche de la mesure la plus recente en poids
            $premier_poids = $em->getRepository(InscriptionMesure::class)->findFirstDataByUserAndMesure($user, $mesurePoids);

        // Initialisation autres variable
            $besoinCalorique = null;
            $imc = null;
            $objectifPoids = null;
            $poidsBase = null;
            $coefPoids = null;

        // Besoin calorique
            if(($user->getAge() !== null && $user->getAge() !== '') && $user->getSexe() !== null && $user->getProfilSportif() !== null && $poids !== null && $taille !== null){
                $besoinCalorique = $this->calculBesoinCaloriqueBase($user->getAge(), $user->getSexe()->getLibelle(), $user->getProfilSportif()->getValue(), $poids->getCm(), $taille->getCm());
            }
            ($poids !== null && $taille !== null)? $imc = $this->imc($taille->getCm(), $poids->getCm()): ''; // IMC
            $mesuresRestantes = $this->mesuresRestantes();  // Mesures restantes
            ($user->getWeightGoal() !== null)? $objectifPoids = $user->getWeightGoal() : '';    // Objectif poids
            ($premier_poids !== null)? $poidsBase = $premier_poids->getCm(): '';    // Premiere inscription  poids
            ($objectifPoids !== null && $poidsBase !== null && $poids !== null)? $coefPoids = $this->calculCoefWeight($objectifPoids, $poidsBase, $poids->getCm()) : ''; // Calcul coef poids
    
        // Retour
            return $this->json(['besoinCal' => $besoinCalorique, 'imc' => $imc, 'mesuresRestante' => $mesuresRestantes, 'objPoids' => $objectifPoids, 'firstPoids' => $poidsBase, 'ActualPoids' => $poids->getCm(), 'coefPoids' => $coefPoids ]);
        }

    
        // Fonction retournant toutes les mesures restantes à inscrire
        function mesuresRestantes(){
            $em = $this->getDoctrine()->getManager();
            $user = $this->getUser();
            $toutes_les_mesures = $em->getRepository(Mesures::class)->findAll();
            $mesure_restantes = 0;
            // Manipulation des dates
                $date_max = new \DateTime();
                $timestamp_max = strtotime($date_max->format('Y-m-d'));
                $une_semaine = 60*60*24*7;
                $timestamp_semaine_derniere = $timestamp_max - $une_semaine;
                $date_debut = new \DateTime();
                date_timestamp_set($date_debut, $timestamp_semaine_derniere);
            
            foreach($toutes_les_mesures as $mesure){
                $inscription = $em->getRepository(InscriptionMesure::class)->findDataOneWeek($user, $mesure, $date_debut, $date_max);
                if(!$inscription){
                    $mesure_restantes++;
                }
            }

            return $mesure_restantes;
        }

    // Fonction retournant le calcul des besoins calorique journaliers de base
        function calculBesoinCaloriqueBase(int $age, String $genreSexe, float $profil_sportif, float $poids, float $taille){
            $resultat = 0;
            if($genreSexe === 'Homme'){
                $resultat = (66.5 + (13.75 * $poids) + (5 * $taille) - (6.77 * $age)) * $profil_sportif;
            } else if ($genreSexe === 'Femme'){
                $resultat = (655 + (9.56 * $poids) + (1.85 * $taille) - (4.67 * $age)) * $profil_sportif;
            }
            return round($resultat,2);
        }

    // Fonction retournant le calcul de l'indice de masse corporelle
        function imc(float $taille, float $poids){
            $tailleMetre = $taille / 100;
            $calcul = $poids / ($tailleMetre * $tailleMetre);
            $resultat = "";

            switch($calcul){
                case $calcul > 40: $resultat = 'Obésite morbide'; break;
                case $calcul <= 40 && $calcul > 35: $resultat = 'Obésité (Classe 2)'; break;
                case $calcul <= 35 && $calcul > 30: $resultat = 'Obésité (Classe 1)'; break;
                case $calcul <= 30 && $calcul > 25: $resultat = 'Surpoids'; break;
                case $calcul <= 25 && $calcul > 18.5: $resultat = 'Corpulence normale'; break;
                case $calcul <= 18.5 && $calcul > 16: $resultat = 'Maigre'; break;
                case $calcul < 16: $resultat = 'Dénutrie'; break;
            }

            return $resultat;
        }

    // Fonction retournant le calcul du coefficient poids 
        function calculCoefWeight(float $obj, float $premier_poids, float $dernier_poids){
            $resultat = 0;
            $valeur_reference = $premier_poids - $obj;
            $valeur_actuelle = $dernier_poids - $obj;
            $resultat = (($valeur_actuelle - $valeur_reference) / $valeur_reference);

            return round(abs($resultat),2);
        }
}
