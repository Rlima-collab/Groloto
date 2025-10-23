<?php

namespace App\Form;

use App\Entity\Tache;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class TacheType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre', TextType::class)
            ->add('posteRequis', ChoiceType::class, [
                'choices' => [
                    'Bar' => 'bar',
                    'Accueil' => 'accueil',
                    'Cuisine' => 'cuisine',
                    'Technique' => 'technique',
                    'Autre' => 'autre',
                ],
                'required' => false,
            ])
            ->add('debut', DateTimeType::class, ['widget' => 'single_text'])
            ->add('fin', DateTimeType::class, ['widget' => 'single_text'])
            ->add('maxPersonnes', IntegerType::class)
            ->add('remarques', TextareaType::class, ['required' => false])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Tache::class,
        ]);
    }
}