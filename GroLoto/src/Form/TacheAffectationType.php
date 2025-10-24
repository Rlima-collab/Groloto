<?php

namespace App\Form;

use App\Entity\Benevole;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TacheAffectationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('benevoles', EntityType::class, [
                'class' => Benevole::class,
                'choice_label' => function(Benevole $benevole) {
                    $utilisateur = $benevole->getUtilisateur();
                    return $utilisateur ? $utilisateur->getPrenom() . ' ' . $utilisateur->getNom() : 'Benevole #' . $benevole->getId();
                },
                'label' => 'Benevoles assignes',
                'multiple' => true,
                'expanded' => true, // Utilise des checkboxes au lieu d'un select
                'required' => false,
                'attr' => [
                    'class' => 'benevoles-checkboxes'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}
