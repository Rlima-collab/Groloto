<?php

namespace App\Form;

use App\Entity\Mecene;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class MeceneEditType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('prenom', TextType::class, [
                'label' => 'Prénom',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Prénom du contact'
                ],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le prénom est obligatoire']),
                    new Assert\Length([
                        'min' => 2,
                        'max' => 50,
                        'minMessage' => 'Le prénom doit contenir au moins {{ limit }} caractères',
                        'maxMessage' => 'Le prénom ne peut pas dépasser {{ limit }} caractères'
                    ])
                ],
                'mapped' => false
            ])
            ->add('nom', TextType::class, [
                'label' => 'Nom',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Nom du contact'
                ],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le nom est obligatoire']),
                    new Assert\Length([
                        'min' => 2,
                        'max' => 50,
                        'minMessage' => 'Le nom doit contenir au moins {{ limit }} caractères',
                        'maxMessage' => 'Le nom ne peut pas dépasser {{ limit }} caractères'
                    ])
                ],
                'mapped' => false
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'email@exemple.com'
                ],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'L\'email est obligatoire']),
                    new Assert\Email(['message' => 'Veuillez entrer une adresse email valide'])
                ],
                'mapped' => false
            ])
            ->add('telephone', TelType::class, [
                'label' => 'Téléphone',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => '06 12 34 56 78'
                ],
                'mapped' => false
            ])
            ->add('organisation', TextType::class, [
                'label' => 'Organisation',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Nom de l\'organisation'
                ],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'L\'organisation est obligatoire']),
                    new Assert\Length([
                        'max' => 255,
                        'maxMessage' => 'L\'organisation ne peut pas dépasser {{ limit }} caractères'
                    ])
                ]
            ])
            ->add('siret', TextType::class, [
                'label' => 'SIRET',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => '12345678901234'
                ],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le SIRET est obligatoire']),
                    new Assert\Length([
                        'min' => 14,
                        'max' => 14,
                        'exactMessage' => 'Le SIRET doit contenir exactement {{ limit }} caractères'
                    ]),
                    new Assert\Regex([
                        'pattern' => '/^\d{14}$/',
                        'message' => 'Le SIRET doit contenir 14 chiffres'
                    ])
                ]
            ])
            ->add('adresse_postale', TextareaType::class, [
                'label' => 'Adresse postale',
                'required' => true,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Adresse complète de l\'organisation',
                    'rows' => 3
                ]
            ])
            ->add('logo', FileType::class, [
                'label' => 'Logo de l\'organisation',
                'required' => false,
                'mapped' => false,
                'attr' => [
                    'class' => 'form-control',
                    'accept' => 'image/png,image/jpeg,image/jpg'
                ],
                'constraints' => [
                    new Assert\File([
                        'maxSize' => '2M',
                        'mimeTypes' => ['image/png', 'image/jpeg', 'image/jpg'],
                        'mimeTypesMessage' => 'Veuillez uploader une image valide (PNG, JPEG)',
                        'maxSizeMessage' => 'Le fichier ne peut pas dépasser 2 Mo'
                    ])
                ],
                'help' => 'Formats acceptés : PNG, JPEG. Taille maximale : 2 Mo'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Mecene::class,
        ]);
    }
}
