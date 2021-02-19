<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Mesures;
use App\Entity\InscriptionMesure;
use App\Entity\User;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use DateTime;

class AppFixtures extends Fixture
{
    public function __construct(UserPasswordEncoderInterface $encoder) {
        $this->encode = $encoder;
    }

    public function load(ObjectManager $manager)
    {

        // Insertion des données des cibles du corps
            $cible1 = new Mesures();
            $cible1->setName('Cou');
            $cible1->setDescription('Sous la pomme d\'Adam au plus petit, décontracté');
            $cible1->setImage('http://menphys.fr/medias/image/cou_menphys_copy2.jpg');
            $manager->persist($cible1);

            $cible2 = new Mesures();
            $cible2->setName('Epaules');
            $cible2->setDescription('Juste au-dessous des clavicules, bras le long du corps, décontracté');
            $cible2->setImage('https://i.ytimg.com/vi/kZnYm_aQkLA/maxresdefault.jpg');
            $manager->persist($cible2);
            
            $cible3 = new Mesures();
            $cible3->setName('Pectoraux');
            $cible3->setDescription('A hauteur des tétons, respiration bloquée normalement, pas avec la poitrine gonflée, décontractée');
            $cible3->setImage('https://entrainement-sportif.fr/pectoraux-mensurations-ideales.jpg');
            $manager->persist($cible3);
            
            $cible4 = new Mesures();
            $cible4->setName('Biceps');
            $cible4->setDescription('Bras levé parallèle au sol dans l\'axe des épaules, avant-bras pliés et biceps contracté, mesurer au plus gros');
            $cible4->setImage('https://www.wikihow.com/images_en/thumb/4/4b/Measure-Biceps-Step-1.jpg/v4-460px-Measure-Biceps-Step-1.jpg.webp');
            $manager->persist($cible4);
            
            $cible5 = new Mesures();
            $cible5->setName('Avant-bras');
            $cible5->setDescription('Avant-bras contracté (poing serré) et bras tendu le long du corps, poignet dans l\'alignement de l\'avant-bras, mesurer au plus gros');
            $cible5->setImage('https://forum.nutrimuscle.com/hebergeur_images/Upload/images/sanstiili.jpg');
            $manager->persist($cible5);
            
            $cible6 = new Mesures();
            $cible6->setName('Poignet');
            $cible6->setDescription('Main ouverte, juste entre la main et la petite protubérance osseuse de l\'avant-bras, décontracté, mesurer au plus petit');
            $cible6->setImage('https://www.bracelet-bresilien.fr/contents/media/mesure-du-tour-de-poignet-bracelet-bresilien-100216-02_.jpg');
            $manager->persist($cible6);
            
            $cible7 = new Mesures();
            $cible7->setName('Tour de taille');
            $cible7->setDescription('Ventre ni rentré ni sorti mais plat, mesurer au plus petit, vers le nombril');
            $cible7->setImage('https://cache.magicmaman.com/data/photo/w800_c18/45/tour-de-taille.jpg');
            $manager->persist($cible7);
            
            $cible8 = new Mesures();
            $cible8->setName('Cuisse');
            $cible8->setDescription('Debout, au plus gros (sous la fesse), décontracté');
            $cible8->setImage('https://i.pinimg.com/originals/c1/6b/82/c16b82d348b364e9e1faec386ca1cb1d.jpg');
            $manager->persist($cible8);
            
            $cible9 = new Mesures();
            $cible9->setName('Mollet');
            $cible9->setDescription('Debout, talon au sol et jambe tendue, au plus gros, décontracté');
            $cible9->setImage('https://lh3.googleusercontent.com/proxy/gXnd36cKZDc7M77yjWVzlkaFUFNB5PczH3zYuyD-sEdKAqMkQJBeB6xqFps-CJYHEIqFzW5oy6c_jFsN7zT2oJpOTg');
            $manager->persist($cible9);
            
            $cible10 = new Mesures();
            $cible10->setName('Cheville');
            $cible10->setDescription('Debout, talon au sol, au plus petit, décontracté');
            $cible10->setImage('https://www.espace-contention.com/images/Image/Image/Prise%20de%20mesures/prise%20de%20mesure%201.jpeg');
            $manager->persist($cible10);
            
            $cible11 = new Mesures();
            $cible11->setName('Hanches');
            $cible11->setDescription('Le mettre-ruban passe sur les fesses et sous le ventre');
            $cible11->setImage('https://i.ytimg.com/vi/SKZVcWWlnEY/maxresdefault.jpg');
            $manager->persist($cible11);
            $manager->flush();
    }
}
