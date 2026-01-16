<?php

namespace App\Form;

use App\Entity\Stock;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class StockType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom de l\'article',
                'attr' => ['placeholder' => 'Ex: Gobelets, Bière, Chaises...']
            ])
            ->add('categorie', ChoiceType::class, [
                'label' => 'Catégorie',
                'choices' => [
                    'Bar' => 'bar',
                    'Restauration' => 'resto',
                    'Décoration' => 'deco',
                    'Autre' => 'autre'
                ]
            ])
            ->add('quantite', IntegerType::class, [
                'label' => 'Quantité initiale',
                'attr' => ['min' => 0]
            ])
            ->add('unite', TextType::class, [
                'label' => 'Unité',
                'attr' => ['placeholder' => 'Ex: pièces, kg, L...']
            ])
            ->add('seuil', IntegerType::class, [
                'label' => 'Seuil d\'alerte',
                'attr' => ['min' => 0]
            ])
            ->add('valeur_unitaire', NumberType::class, [
                'label' => 'Valeur unitaire (€)',
                'scale' => 2,
                'attr' => ['step' => '0.01', 'min' => 0]
            ])
            ->add('source', ChoiceType::class, [
                'label' => 'Source',
                'choices' => [
                    'Achat' => 'achat',
                    'Prêt' => 'pret',
                    'Don' => 'don'
                ],
                'expanded' => true
            ])
            ->add('preteur', TextType::class, [
                'label' => 'Prêteur (si prêt)',
                'required' => false,
                'attr' => ['placeholder' => 'Nom du prêteur']
            ])
            ->add('date_retour', DateType::class, [
                'label' => 'Date de retour prévue (si prêt)',
                'required' => false,
                'widget' => 'single_text'
            ])
            ->add('remarque', TextareaType::class, [
                'label' => 'Remarques',
                'required' => false,
                'attr' => ['rows' => 3]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Stock::class,
        ]);
    }
}
