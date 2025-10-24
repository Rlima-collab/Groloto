<?php

namespace App\Form;

use App\Entity\Tache;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class TacheType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre', TextType::class, ['label' => 'Titre'])
            ->add('poste_requis', ChoiceType::class, [
                'choices' => [
                    'Bar' => 'bar',
                    'Accueil' => 'accueil',
                    'Cuisine' => 'cuisine',
                    'Technique' => 'technique',
                    'Autre' => 'autre',
                ],
                'label' => 'Poste requis',
            ])
            ->add('debut', DateTimeType::class, ['widget' => 'single_text', 'label' => 'Début'])
            ->add('fin', DateTimeType::class, ['widget' => 'single_text', 'label' => 'Fin'])
            ->add('max_personnes', null, ['label' => 'Max personnes'])
            ->add('remarque', TextareaType::class, ['required' => false, 'label' => 'Remarque']);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Tache::class,
        ]);
    }
}