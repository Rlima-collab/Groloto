<?php

namespace App\Form;

use App\Entity\Tache;
use App\Entity\Weekend;
use App\Entity\Benevole;
use App\Repository\WeekendRepository;
use App\Repository\BenevoleRepository;
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

        // Ajouter le champ bénévoles uniquement pour la création
        if ($options['include_benevoles']) {
            $builder->add('benevoles', EntityType::class, [
                'class' => Benevole::class,
                'query_builder' => fn(BenevoleRepository $br) => $br->createQueryBuilder('b')
                    ->innerJoin('b.utilisateur', 'u')
                    ->where('b.actif = :actif')
                    ->setParameter('actif', true)
                    ->orderBy('u.nom', 'ASC'),
                'choice_label' => fn(Benevole $b) => $b->getUtilisateur()->getPrenom() . ' ' . $b->getUtilisateur()->getNom(),
                'label' => 'Proposer aux bénévoles',
                'multiple' => true,
                'expanded' => true,
                'mapped' => false,
                'required' => false,
                'attr' => ['class' => 'benevoles-checkboxes'],
                'choice_attr' => function($choice, $key, $value) {
                    return ['class' => 'benevole-checkbox'];
                },
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Tache::class,
            'include_benevoles' => true,
        ]);
    }
}