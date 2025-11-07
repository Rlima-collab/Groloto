<?php

namespace App\Form;

use App\Entity\Evenement;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Positive;

class EvenementEmbeddedType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom de l\'événement',
                'attr' => ['class' => 'w-full p-3 border rounded-lg']
            ])
            // PAS de champ weekend ici - il sera automatiquement lié au weekend parent
            ->add('date_debut', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date de l\'événement',
                'required' => true,
                'attr' => ['class' => 'w-full p-3 border rounded-lg']
            ])
            ->add('heure_debut', TimeType::class, [
                'widget' => 'single_text',
                'label' => 'Heure de début',
                'required' => true,
                'attr' => ['class' => 'w-full p-3 border rounded-lg']
            ])
            ->add('duree_minutes', IntegerType::class, [
                'label' => 'Durée (en minutes)',
                'required' => true,
                'attr' => [
                    'class' => 'w-full p-3 border rounded-lg',
                    'min' => 1
                ],
                'constraints' => [
                    new Positive(message: 'La durée doit être positive')
                ]
            ])
            ->add('lieu', TextType::class, [
                'label' => 'Lieu',
                'required' => false,
                'attr' => ['class' => 'w-full p-3 border rounded-lg']
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
                'attr' => ['rows' => 4, 'class' => 'w-full p-3 border rounded-lg']
            ])
            ->add('imageFile', FileType::class, [
                'label' => 'Image de l\'événement',
                'mapped' => false,
                'required' => false,
                'attr' => ['class' => 'w-full p-3 border rounded-lg', 'accept' => 'image/*'],
                'constraints' => [
                    new File([
                        'maxSize' => '5M',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/jpg',
                            'image/png',
                            'image/gif',
                            'image/webp',
                        ],
                        'mimeTypesMessage' => 'Veuillez télécharger une image valide (JPEG, PNG, GIF ou WebP)',
                    ])
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Evenement::class,
        ]);
    }
}
