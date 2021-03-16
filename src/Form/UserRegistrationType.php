<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;

class UserRegistrationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => false,
                'attr'  => ['placeholder'   => 'Adresse de messagerie']
            ])
            ->add('firstname', TextType::class, [
                'label' => false, 
                'attr'  => ['placeholder'   => 'Prénom'],
                'required'  => true
            ])
            ->add('lastname', TextType::class, [
                'label' => false, 
                'attr'  => ['placeholder'   => 'Nom de famille'],
                'required'  => true
            ])
            ->add('password', RepeatedType::class, [
                'type'      => PasswordType::class,
                'first_options'  => ['attr' => ['placeholder'  => 'Mot de passe'], 'label'     => false,],
                'second_options' => ['attr' => ['placeholder'  => 'Répétez mot de passe'], 'label'     => false,],
            ])
            ->add('date_naissance', DateType::class, [
                'label' => "Date de naissance", 
                'required'  => true,
                    'widget' => 'single_text',
                
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'S\'inscrire',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
