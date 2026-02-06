<?php
namespace App\Form;

use App\Dto\ContactDto;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Email as EmailConstraint;

class ContactForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Votre nom',
                'attr' => ['placeholder' => 'Jean Dupont'],
                'required' => true,
                'constraints' => [
                    new NotBlank(message: 'Le nom est requis.'),
                ],
            ])
            ->add('email', EmailType::class, [
                'label' => 'Votre email',
                'attr' => ['placeholder' => 'jean@example.com'],
                'required' => true,
                'constraints' => [
                    new NotBlank(message: 'L\'email est requis.'),
                    new EmailConstraint(message: 'L\'adresse email n\'est pas valide.'),
                ],
            ])
            ->add('sujet', TextType::class, [
                'label' => 'Sujet',
                'required' => true,
                'constraints' => [
                    new NotBlank(message: 'Le sujet est requis.'),
                ],
                'attr' => ['placeholder' => 'Ex: Demande d\'information'],
            ])
            ->add('message', TextareaType::class, [
                'label' => 'Votre message',
                'attr' => ['rows' => 5, 'placeholder' => 'Écrivez votre message ici...'],
                'required' => true,
                'constraints' => [
                    new NotBlank(message: 'Le message est requis.'),
                ],
            ])
            ->add('envoyer', SubmitType::class, [
                'label' => 'Envoyer',
                'attr' => ['class' => 'btn btn-primary btn-lg px-5'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ContactDto::class,
        ]);
    }
}