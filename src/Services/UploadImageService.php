<?php 
namespace App\Services;

use App\Entity\User;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * /////////////////////// METHOD ///////////////////////////
 * verifyUploadedFile
 * 
 * /////////////////////// FONCTIONS ////////////////////////
 * verifyExtFile
 * verifyTypeMime
 * verifyFileExist
 * removeFile
 * 
 */
class UploadImageService
{
    private $size_authorized = 5 * 1024 * 1024;
    private $type_authorized = ["jpg" => "image/jpg", "jpeg" => "image/jpeg", "gif" => "image/gif", "png" => "image/png"];

    /* Récupération du fichier pour le vérifier */
        public function verifyUploadedFile($fichier, $user, ObjectManager $em, $folder_dir){
            // Variables 
                $message = [];
                $status = $fichier['photo_profil']['error'];
                $name = $fichier['photo_profil']['name'];
                $type = $fichier['photo_profil']['type'];
                $size = $fichier['photo_profil']['size'];

            // Traitement
                // Vérification erreur
                    if($status !== 0){                                                              
                        return $message[] = ['Erreur' => '<div class="text-danger text-center">Erreur dans le téléchargement de votre fichier.</div>'];
                    } 

                // Vérification extension
                    if(!array_key_exists(pathinfo($name, PATHINFO_EXTENSION), $this->type_authorized)){                                                         
                        return $message[] = ['Erreur' => '<div class="text-danger text-center">L\'extension de votre fichier n\'est pas conforme.<br /> Extension attendue: jpg/jpeg/gif/png.</div>'];
                    }
                
                // Vérification poids/taille
                    if($size > $this->size_authorized){
                        return $message[] = ['Erreur' => '<div class="text-danger text-center">Le taille du fichier est supérieur à la taille maximale autorisée.<br />Taille maximale: 5 Mo.</div>'];
                    }

                // Vérification du type MIME
                    if(!in_array($type, $this->type_authorized)){
                        return $message[] = ['Erreur' => '<div class="text-danger text-center">Le type du fichier n\'est pas correct.</div>'];
                    }

            // Si aucune erreur
                if(count($message) === 0){
                    // On verifie si l'utilisateur a déjà une photo de profil
                        if($user->getImageProfil() != null){
                            unlink($folder_dir . '/' . $user->getImageProfil());
                        } 
                        // Changement du nom du fichier
                            $fichier['photo_profil']['name'] = $this->renameFile($user, $name);

                        // Enregistrement en base de données
                            $user->setImageProfil($fichier['photo_profil']['name']);
                            $em->flush();

                        // Upload du fichier    
                            move_uploaded_file($fichier["photo_profil"]["tmp_name"], $folder_dir . '/' . $fichier['photo_profil']['name']);

                            return $message[] = ['success' => '<div class="text-success text-center>"Votre photo a correctement été ajoutée.</div>', 'photo_name' => $fichier['photo_profil']['name']];
                }
                    

        }

    /* Fonction de remplacement du nom du fichier */
        public function renameFile($user, $name){
            $tab = explode('.', $name);
            $new_name = $user->getId() . '-photo-profil-' . $tab[0] . '.' . $tab[1];
            return $new_name;
        }
}
