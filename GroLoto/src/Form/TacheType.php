<?php

namespace App\Form;

use App\Entity\Tache;
use App\Entity\Weekend;
use App\Entity\Evenement;                // <-- Ajouté
use App\Repository\WeekendRepository;
use App\Repository\EvenementRepository;   // <-- Ajouté
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;

class TacheType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre', TextType::class, [
                'label' => 'Titre de la tâche',
                'attr' => [
                    'class'       => 'form-control',
                    'placeholder' => 'Ex: Vente de billets',
                ],
            ])
            ->add('debut', DateTimeType::class, [
                'label'  => 'Date et heure de début',
                'widget' => 'single_text',
                'attr'   => ['class' => 'form-control'],
            ])
            ->add('fin', DateTimeType::class, [
                'label'  => 'Date et heure de fin',
                'widget' => 'single_text',
                'attr'   => ['class' => 'form-control'],
            ])
            ->add('posteRequis', TextType::class, [
                'label'    => 'Poste requis',
                'required' => false,
                'attr'     => [
                    'class'       => 'form-control',
                    'placeholder' => 'Ex: Vendeur, Caissier, Accueil, Bar, Cuisine...',
                ],
            ])
            ->add('maxPersonnes', IntegerType::class, [
                'label'    => 'Nombre maximum de personnes',
                'required' => false,
                'attr'     => [
                    'class'       => 'form-control',
                    'min'         => 1,
                    'placeholder' => 'Ex: 5',
                ],
            ])
            ->add('remarque', TextareaType::class, [
                'label'    => 'Remarques',
                'required' => false,
                'attr'     => [
                    'class'       => 'form-control',
                    'rows'        => 4,
                    'placeholder' => 'Informations complémentaires...',
                ],
            ])

            // ───── ÉVÉNEMENT ─────
            ->add('evenement', EntityType::class, [
                'class'         => \App\Entity\Evenement::class,
                'query_builder' => function (EvenementRepository $er) {
                    return $er->createQueryBuilder('e')
                        ->where('e.date_debut >= :oneWeekAgo')
                        ->setParameter('oneWeekAgo', new \DateTime('-1 week'))
                        ->orderBy('e.date_debut', 'ASC');
                },
                'choice_label' => function ($evenement) {
                    $dateDebut = $evenement->getDateDebut()?->format('d/m/Y') ?? '';
                    $dateFin   = $evenement->getDateFin()?->format('d/m/Y') ?? '';

                    if ($dateDebut && $dateFin) {
                        return $dateDebut === $dateFin
                            ? sprintf('%s - %s', $evenement->getNom(), $dateDebut)
                            : sprintf('%s - Du %s au %s', $evenement->getNom(), $dateDebut, $dateFin);
                    }

                    return $dateDebut
                        ? sprintf('%s - %s', $evenement->getNom(), $dateDebut)
                        : $evenement->getNom();
                },
                'label'    => 'Événement associé',
                'required' => false,
                'attr'     => ['class' => 'form-control'],
            ])

            // ───── WEEKEND ─────
            ->add('weekend', EntityType::class, [
                'class'         => Weekend::class,
                'query_builder' => function (WeekendRepository $wr) {
                    return $wr->createQueryBuilder('w')
                        ->where('w.date_dimanche >= :today')
                        ->setParameter('today', new \DateTime())
                        ->orderBy('w.date_vendredi', 'ASC');
                },
                'choice_label' => function (Weekend $weekend) {
                    return sprintf(
                        '%s (du %s au %s)',
                        $weekend->getNom(),
                        $weekend->getDateVendredi()->format('d/m/Y'),
                        $weekend->getDateDimanche()->format('d/m/Y')
                    );
                },
                'label'       => 'Weekend associé',
                'required'    => true,
                'placeholder' => 'Sélectionner un weekend',
                'attr'        => ['class' => 'form-control'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Tache::class,
        ]);
    }
}