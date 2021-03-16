<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

use App\Entity\User;
use App\Form\UserRegistrationType;
use Swift_SmtpTransport;
use Swift_Mailer;
use Swift_Message;
use \DateTime;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class GateController extends AbstractController
{
    /**
     * @Route("/", name="")
     */
    public function index(): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('mesure');
        }

        return $this->render('gate/index.html.twig');
    }

    /**
     * @Route("/registration", name="registration")
     */
    public function registration(Request $request, UserPasswordEncoderInterface $passwordEncoder){
        if ($this->getUser()) {
            return $this->redirectToRoute('mesure');
        }

        $em = $this->getDoctrine()->getManager();
        $user = new User();
        $form = $this->createForm(UserRegistrationType::class, $user);

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $password = $passwordEncoder->encodePassword($user, $user->getPassword());
            $user->setPassword($password);
            $em->persist($user);
            $em->flush();

            return $this->redirectToRoute('app_login');
        }

        return $this->render('gate/registration.html.twig', [
            'formRegistration'  => $form->createView()
        ]);
    }

    /**
     * @Route("/login", name="app_login")
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_homepage');
        }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    /**
     * @Route("/logout", name="app_logout")
     */
    public function logout()
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    /**
     * @Route("forgot_password", name="forgot_passord")
     */
    public function forgotPassword(Request $request){
        return $this->render('gate/forgot_password.html.twig');
    }

    /**
     * @Route("send_mail_password", name="send_mail_password", options={"expose"=true} )
     */
    public function sendMailPassword(Request $request, \Swift_Mailer $mailer){
        $data = $request->request->get("data");
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository(User::class)->findOneBy(["email" => $data]);
        $error = "";

        if(!filter_var($data, FILTER_VALIDATE_EMAIL)){
            $error = "Adresse mail non valide.";
        } else {
            if($user != null){
    
                // Création et enregistrement d'un token de vérification
                    $date = new DateTime('02/26/2021');
                    $token = bin2hex(random_bytes(30));
                    $user->setTokenRecover($token);
                    $user->setPasswordChangeAsked(true);
                    $em->flush();
    
                // Création et envoi du message
                    $transport = (new Swift_SmtpTransport('smtp.gmail.com', 465, 'ssl'))
                    ->setUsername('mesmesures00@gmail.com')
                    ->setPassword('Platinum#00');
                    $mailer = new Swift_Mailer($transport);
                    $message = (new Swift_Message("Récupération de mot de passe"))
                        ->setFrom("mesmesures@outlook.fr")
                        ->setTo($user->getEmail())
                        ->setBody(
                            $this->renderView("emails/recuperation_password.html.twig", ['user' => $user, "token" => $token]), 
                            "text/html"
                        )
                    ;
                    $mailer->send($message);
            }
        }

        return new JsonResponse($error);
    }

    /**
     * @Route("change_password", name="change_password")
     */
     public function change_password(){
        return $this->render('gate/change_password.html.twig');
     }

    /**
     * @Route("set_new_password", name="set_new_password", options={"expose"=true} )
     */
    public function setNewPassword(Request $request, UserPasswordEncoderInterface $passwordEncoder){
        // Connexion base de données
            $em = $this->getDoctrine()->getManager();
        // Récupération des variables
            $mail   = $request->request->get('email');
            $token  = $request->request->get('token');
            $pass1  = $request->request->get('pass1');
            $pass2  = $request->request->get('pass2');
            $error  = null;
            $regex  = "/^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*#?&])[A-Za-z\d@$!%*#?&]{8,}$/";

        // Vérification mail et token
            $user = $em->getRepository(User::class)->findOneBy(['email' => $mail, 'token_recover' => $token]);

        // Vérification validité du token et existence de l'utilisateur associé
            if($user == null){
                $error = "Utilisateur introuvable.";
            } else {
                // Vérification changement demandé
                if($user->getPasswordChangeAsked() == false){
                    $error = 'Aucune demande de mot de passe exprimée.';
                } else {
                    // Vérification complétion des mots de passe et réciprocité
                        if($pass1 == null || $pass2 == null || $pass1 != $pass2){
                            $error = "Mot de passe manquant ou non identiques.";
                        } else {
                            // Vérification validité des mots de passe
                                if(!preg_match($regex, $pass1)){
                                    $error = "Mot de passe non conforme.";
                                } else {
                                    $password = $passwordEncoder->encodePassword($user, $pass1);
                                    $user->setPassword($password);
                                    $user->setPasswordChangeAsked(false);
                                    $em->flush();
                                }
                        }
                }
            }

        // Retour
            return $this->json($error);
    }
}
