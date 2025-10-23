<?php

namespace App\Form;

use App\Entity\Evenement;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class EvenementType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class)
            ->add('description', TextareaType::class, ['required' => false])
            ->add('lieu', TextType::class, ['required' => false])
            ->add('dateVendredi', DateType::class, ['widget' => 'single_text', 'label' => 'Date du vendredi principal'])
            ->add('includeJM2', CheckboxType::class, ['label' => 'Inclure J-2', 'required' => false])
            ->add('includeJM1', CheckboxType::class, ['label' => 'Inclure J-1', 'required' => false])
            ->add('includeJP1', CheckboxType::class, ['label' => 'Inclure J+1', 'required' => false])
            ->add('taches', CollectionType::class, [
                'entry_type' => TacheType::class,
                'entry_options' => ['label' => false],
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Evenement::class,
        ]);
    }
}