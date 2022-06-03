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

class RegistrationFormAdmin extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('roles', ChoiceType::class, [
                'required' => true,
                'multiple' => false,
                'expanded' => false,
                'choices' => [
                    'Centre' => 'ROLE_CENTER',
                    'Formateur' => 'ROLE_TRAINER',
                    'Administrateur' => 'ROLE_ADMIN',
                ],
            ])
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
            ->add('agreeTerms', CheckboxType::class, [
                'label' => 'J\'accepte les conditions d\'utilisations',
                'mapped' => false,
                'constraints' => [
                    new IsTrue([
                        'message' => 'Vous devez accepter nos conditions d\'utilisation.',
                    ]),
                ],
            ])
            ->add('plainPassword', PasswordType::class, [
                'label' => false,
                'mapped' => false,
                'attr' => [
                    'autocomplete' => 'new-password',
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

        // Data transformer
        $builder->get('roles')
            ->addModelTransformer(new CallbackTransformer(
                function ($rolesArray) {
                    // transform the array to a string
                    return count($rolesArray) ? $rolesArray[0] : null;
                },
                function ($rolesString) {
                    // transform the string back to an array
                    return [$rolesString];
                }
            ));

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
