<?php

namespace App\Form;

use App\Entity\Weekend;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class WeekendType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom du weekend',
                'attr' => ['class' => 'w-full p-3 border rounded-lg']
            ])
            ->add('dateVendredi', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date du vendredi',
                'data' => new \DateTime('next friday'),
                'mapped' => false,
                'attr' => ['class' => 'w-full p-3 border rounded-lg']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Weekend::class]);
    }
}