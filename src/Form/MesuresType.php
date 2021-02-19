<?php

namespace App\Form;

use App\Entity\Mesures;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class MesuresType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'label' => false,
                'attr'  => [ 'placeholder' => "Nom"]
            ])
            ->add('title', TextType::class, [
                'label' => false,
                'attr'  => [ 'placeholder' => "Titre"]
            ])
            ->add('description', TextareaType::class, [
                'label' => false,
                'attr'  => [ 'placeholder' => "Description"]
            ])
            ->add('submit', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Mesures::class,
        ]);
    }
}
