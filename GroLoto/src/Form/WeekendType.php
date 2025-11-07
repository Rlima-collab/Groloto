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
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints\File as FileConstraint;

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
                'label' => 'Date de début',
                'attr' => [
                    'class' => 'w-full p-3 border rounded-lg',
                    'placeholder' => 'Date de début du weekend'
                ],
                'help' => 'Sélectionnez la date de début du weekend'
            ])
            ->add('date_dimanche', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date de fin',
                'attr' => [
                    'class' => 'w-full p-3 border rounded-lg',
                    'placeholder' => 'Date de fin du weekend'
                ],
                'help' => 'Sélectionnez la date de fin du weekend (minimum 2 jours au total)'
            ])

            ->add('cover_image', FileType::class, [
                'label' => 'Image de couverture (optionnelle)',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new FileConstraint([
                        'maxSize' => '5M',
                        'mimeTypes' => ['image/jpeg', 'image/png', 'image/webp'],
                        'mimeTypesMessage' => 'Veuillez télécharger une image JPG, PNG ou WebP valide',
                    ])
                ],
                'attr' => ['class' => 'w-full']
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