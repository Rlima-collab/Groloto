<?php

namespace App\Form;

use App\Entity\Tache;
use App\Entity\Evenement;
use App\Repository\EvenementRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class TacheType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre', TextType::class, [
                'label' => 'Titre de la tâche',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ex: Vente de billets'
                ]
            ])
            ->add('debut', DateTimeType::class, [
                'label' => 'Date et heure de début',
                'widget' => 'single_text',
                'attr' => ['class' => 'form-control']
            ])
            ->add('fin', DateTimeType::class, [
                'label' => 'Date et heure de fin',
                'widget' => 'single_text',
                'attr' => ['class' => 'form-control']
            ])
            ->add('posteRequis', TextType::class, [
                'label' => 'Poste requis',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ex: Vendeur, Caissier, Accueil, Bar, Cuisine...'
                ]
            ])
            ->add('maxPersonnes', IntegerType::class, [
                'label' => 'Nombre maximum de personnes',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'min' => 1,
                    'placeholder' => 'Ex: 5'
                ]
            ])
            ->add('remarque', TextareaType::class, [
                'label' => 'Remarques',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 4,
                    'placeholder' => 'Informations complémentaires...'
                ]
            ])
            ->add('evenement', EntityType::class, [
                'class' => Evenement::class,
                'query_builder' => function (EvenementRepository $repository) {
                    return $repository->createQueryBuilder('e')
                        ->where('e.date_debut >= :oneWeekAgo')
                        ->setParameter('oneWeekAgo', new \DateTime('-1 week'))
                        ->orderBy('e.date_debut', 'ASC');
                },
                'choice_label' => function(Evenement $evenement) {
                    $dateDebut = $evenement->getDateDebut() ? $evenement->getDateDebut()->format('d/m/Y') : '';
                    $dateFin = $evenement->getDateFin() ? $evenement->getDateFin()->format('d/m/Y') : '';
                    
                    if ($dateDebut && $dateFin) {
                        if ($dateDebut === $dateFin) {
                            return sprintf('%s - %s', $evenement->getNom(), $dateDebut);
                        } else {
                            return sprintf('%s - Du %s au %s', $evenement->getNom(), $dateDebut, $dateFin);
                        }
                    } elseif ($dateDebut) {
                        return sprintf('%s - %s', $evenement->getNom(), $dateDebut);
                    }
                    
                    return $evenement->getNom();
                },
                'label' => 'Événement associé',
                'required' => false,
                'placeholder' => 'Sélectionner un événement',
                'attr' => ['class' => 'form-control']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Tache::class,
        ]);
    }
}