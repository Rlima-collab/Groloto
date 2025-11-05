<?php

namespace App\Form;

use App\Entity\Weekend;
use App\Entity\Evenement;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
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
                'label' => 'Date de départ (Vendredi)',
                'attr' => [
                    'class' => 'w-full p-3 border rounded-lg',
                    'placeholder' => 'Date de début du weekend'
                ],
                'help' => 'Sélectionnez la date du vendredi (premier jour du weekend)'
            ])
            ->add('nombre_jours', IntegerType::class, [
                'label' => 'Nombre de jours',
                'mapped' => false,
                'data' => 3,
                'attr' => [
                    'class' => 'w-full p-3 border rounded-lg',
                    'min' => 1,
                    'max' => 7,
                    'placeholder' => '3'
                ],
                'help' => 'Nombre de jours du weekend (par défaut: 3 jours - vendredi, samedi, dimanche)'
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