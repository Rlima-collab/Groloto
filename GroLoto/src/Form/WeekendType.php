<?php

namespace App\Form;

use App\Entity\Weekend;
use App\Entity\Evenement;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class WeekendType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom du weekend',
                'attr' => ['class' => 'w-full p-3 border rounded-lg']
            ])
            ->add('date_vendredi', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Vendredi',
                'attr' => ['class' => 'w-full p-3 border rounded-lg']
            ])
            ->add('date_samedi', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Samedi',
                'attr' => ['class' => 'w-full p-3 border rounded-lg']
            ])
            ->add('date_dimanche', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Dimanche',
                'attr' => ['class' => 'w-full p-3 border rounded-lg']
            ])

            // ÉVÉNEMENTS IMBRIQUÉS
            ->add('evenements', CollectionType::class, [
                'entry_type' => EvenementType::class,
                'entry_options' => ['label' => false],
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'prototype' => true,
                'attr' => ['class' => 'space-y-4']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Weekend::class,
        ]);
    }
}