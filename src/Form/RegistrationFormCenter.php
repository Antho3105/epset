<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class RegistrationFormCenter extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('userName', TextType::class, [
                'label' => false,
                'attr' => [
                    'placeholder' => 'Nom d\'utilisateur',
                    'maxlength' => 40
                ],
                'constraints' => new Length([
                    'max' => 40,
                    'maxMessage' => '{{ limit }} caractères maximum',
                ])
            ])
            ->add('lastName', TextType::class, [
                'label' => false,
                'attr' => [
                    'placeholder' => 'Nom',
                    'maxlength' => 40
                ],
                'constraints' => new Length([
                    'max' => 40,
                    'maxMessage' => '{{ limit }} caractères maximum',
                ])
            ])
            ->add('firstName', TextType::class, [
                'label' => false,
                'attr' => [
                    'placeholder' => 'Prénom',
                    'maxlength' => 30
                ],
                'constraints' => new Length([
                    'max' => 30,
                    'maxMessage' => '{{ limit }} caractères maximum',
                ])
            ])
            ->add('email', EmailType::class, [
                'label' => false,
                'attr' => [
                    'placeholder' => 'Email',
                    'maxlength' => 60
                ],
                'constraints' => new Length([
                    'max' => 60,
                    'maxMessage' => '{{ limit }} caractères maximum autorisés',
                ])
            ])
            ->add('phone', TelType::class, [
                'label' => false,
                'attr' => [
                    'placeholder' => 'Téléphone',
                    'maxlength' => 25
                ],
                'constraints' => new Length([
                    'max' => 25,
                    'maxMessage' => '{{ limit }} caractères maximum autorisés',
                ])
            ])
            ->add('domain', TextType::class, [
                'label' => false,
                'attr' => [
                    'placeholder' => 'Domaines d\'activité',
                    'maxlength' => 100
                ],
                'constraints' => new Length([
                    'max' => 1030,
                    'maxMessage' => '{{ limit }} caractères maximum',
                ])
            ])
            ->add('plainPassword', PasswordType::class, [
                'label' => false,
                // instead of being set onto the object directly,
                // this is read and encoded in the controller
                'mapped' => false,
                'attr' => ['autocomplete' => 'new-password',
                    'placeholder' => 'Mot de passe'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Merci de saisir un mot de passe.',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Le mot de passe doit contenir au moins {{ limit }} caractères',
                        // max length allowed by Symfony for security reasons
                        'max' => 4096,
                    ]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
