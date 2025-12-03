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
    DateTimeType
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
            ->add('debut', DateTimeType::class, [
                'label' => 'Date et heure de début',
                'widget' => 'single_text',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('fin', DateTimeType::class, [
                'label' => 'Date et heure de fin',
                'widget' => 'single_text',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('maxPersonnes', IntegerType::class, [
                'label' => 'Nombre max de personnes',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'min' => 1,
                    'placeholder' => 'Ex: 5'
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
            ])

            // WEEKEND UNIQUEMENT
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
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Tache::class,
        ]);
    }
}