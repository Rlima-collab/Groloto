<?php

namespace App\Form;

use App\Entity\Tache;
use App\Entity\Weekend;
use App\Repository\WeekendRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\{
    TextType,
    TextareaType,
    IntegerType,
    DateType,
    TimeType
};

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
                ],
            ])
            ->add('weekend', EntityType::class, [
                'class' => Weekend::class,
                'query_builder' => fn(WeekendRepository $wr) => $wr->createQueryBuilder('w')
                    ->where('w.date_fin >= :today')
                    ->setParameter('today', new \DateTime())
                    ->orderBy('w.date_debut', 'ASC'),
                'choice_label' => fn(Weekend $w) => sprintf(
                    '%s (du %s au %s)',
                    $w->getNom(),
                    $w->getDateDebut()->format('d/m/Y'),
                    $w->getDateFin()->format('d/m/Y')
                ),
                'label' => 'Weekend associé',
                'placeholder' => 'Choisir un weekend',
                'required' => true,
                'attr' => ['class' => 'form-control'],
                'choice_attr' => function(Weekend $w, $key, $index) {
                    return [
                        'data-start' => $w->getDateDebut()->format('Y-m-d'),
                        'data-end' => $w->getDateFin()->format('Y-m-d'),
                        'data-offset-before' => (string) $w->getTaskOffsetBefore(),
                        'data-offset-after' => (string) $w->getTaskOffsetAfter(),
                    ];
                },
            ])
            // Plage horaire unique pour la création simple
            ->add('jour_plage', DateType::class, [
                'label' => 'Jour',
                'widget' => 'single_text',
                'attr' => ['class' => 'form-control'],
                'mapped' => false,
                'required' => false,
            ])
            ->add('heure_debut_plage', TimeType::class, [
                'label' => 'Heure de début',
                'widget' => 'single_text',
                'attr' => ['class' => 'form-control'],
                'mapped' => false,
                'required' => false,
            ])
            ->add('heure_fin_plage', TimeType::class, [
                'label' => 'Heure de fin',
                'widget' => 'single_text',
                'attr' => ['class' => 'form-control'],
                'mapped' => false,
                'required' => false,
            ])
            ->add('maxPersonnes', IntegerType::class, [
                'label' => 'Nombre max de personnes (global)',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'min' => 1,
                    'placeholder' => 'Laisser vide pour illimité'
                ],
            ])
            ->add('remarque', TextareaType::class, [
                'label' => 'Remarques',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 4,
                    'placeholder' => 'Infos supplémentaires...'
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Tache::class,
        ]);
    }
}