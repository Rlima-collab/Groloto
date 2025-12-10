<?php
namespace App\Form;

use App\Entity\InscriptionMecene;
use App\Entity\Evenement;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\EntityRepository;

class InscriptionMeceneType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('evenement', EntityType::class, [
                'class' => Evenement::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('e')
                        ->where('e.date_debut >= :today')
                        ->setParameter('today', new \DateTime())
                        ->orderBy('e.date_debut', 'ASC');
                },
                'choice_label' => function(Evenement $evenement) {
                    $date = $evenement->getDateDebut() ? $evenement->getDateDebut()->format('d/m/Y') : 'Date non définie';
                    return $evenement->getNom() . ' - ' . $date;
                },
                'label' => 'Événement',
                'placeholder' => 'Sélectionnez un événement',
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Veuillez sélectionner un événement'])
                ]
            ])
            ->add('type_don', ChoiceType::class, [
                'label' => 'Type de don',
                'choices' => [
                    'Don pour le fonctionnement du festival' => 'fonctionnement',
                    'Don pour les lots' => 'lot'
                ],
                'expanded' => true,
                'multiple' => false,
                'required' => true,
                'data' => 'fonctionnement'
            ])
            ->add('nom_don', TextType::class, [
                'label' => 'Nom du don',
                'help' => 'Nom de l\'article ou du lot à ajouter au stock',
                'required' => true,
                'attr' => [
                    'placeholder' => 'Ex: Bouteilles de vin, Panier garni, Pack de boissons...'
                ],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Veuillez indiquer le nom du don']),
                    new Assert\Length([
                        'min' => 3,
                        'max' => 255,
                        'minMessage' => 'Le nom doit contenir au moins {{ limit }} caractères',
                        'maxMessage' => 'Le nom ne peut pas dépasser {{ limit }} caractères'
                    ])
                ]
            ])
            ->add('categorie', ChoiceType::class, [
                'label' => 'Catégorie',
                'placeholder' => 'Sélectionnez une catégorie',
                'required' => true,
                'choices' => [
                    'Bar (boissons, alcools...)' => 'bar',
                    'Restauration (nourriture, ingrédients...)' => 'resto',
                    'Décoration (matériel, décors...)' => 'deco',
                    'Autre' => 'autre'
                ],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Veuillez sélectionner une catégorie'])
                ]
            ])
            ->add('quantite', IntegerType::class, [
                'label' => 'Quantité',
                'required' => true,
                'attr' => [
                    'placeholder' => '1',
                    'min' => '1'
                ],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Veuillez indiquer la quantité']),
                    new Assert\Positive(['message' => 'La quantité doit être positive'])
                ]
            ])
            ->add('valeur_unitaire', NumberType::class, [
                'label' => 'Valeur unitaire (€)',
                'help' => 'Valeur estimée par unité',
                'required' => false,
                'attr' => [
                    'placeholder' => '0.00',
                    'step' => '0.01',
                    'min' => '0'
                ]
            ])
            ->add('description_don', TextareaType::class, [
                'label' => 'Description détaillée',
                'help' => 'Détails supplémentaires sur votre don',
                'required' => true,
                'attr' => [
                    'rows' => 4,
                    'placeholder' => 'Ex: 10 bouteilles de vin rouge AOC Bordeaux, millésime 2020...'
                ],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Veuillez décrire votre don']),
                    new Assert\Length([
                        'min' => 10,
                        'max' => 1000,
                        'minMessage' => 'La description doit contenir au moins {{ limit }} caractères',
                        'maxMessage' => 'La description ne peut pas dépasser {{ limit }} caractères'
                    ])
                ]
            ])
            ->add('remarques', TextareaType::class, [
                'label' => 'Remarques complémentaires',
                'help' => 'Informations supplémentaires, contraintes, conditions...',
                'required' => false,
                'attr' => [
                    'rows' => 3,
                    'placeholder' => 'Informations complémentaires...'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => InscriptionMecene::class,
        ]);
    }
}
