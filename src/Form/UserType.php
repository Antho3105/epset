<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('lastName', TextType::class, [
                'label' => 'Nom',
                'attr' => ['maxlength' => 40],
                'constraints' => [
                    new Length([
                        'max' => 40,
                        'maxMessage' => '{{ limit }} caractères maximum autorisés',
                    ]),
                    new NotBlank([
                        "message" => "Vous devez renseigner un nom"
                    ])],
            ])
            ->add('firstName', TextType::class, [
                'label' => 'Prénom',
                'attr' => ['maxlength' => 30],
                'constraints' => [
                    new Length([
                        'max' => 30,
                        'maxMessage' => '{{ limit }} caractères maximum autorisés',
                    ]),
                    new NotBlank([
                        "message" => "Vous devez renseigner un prénom"
                    ])
                ]
            ])
            ->add('phone', TelType::class, [
                'label' => 'Téléphone',
                'attr' => ['maxlength' => 25],
                'constraints' => [
                    new Length([
                        'max' => 25,
                        'maxMessage' => '{{ limit }} caractères maximum autorisés',
                    ]),
                    new NotBlank([
                        "message" => "Vous devez renseigner un numéro de téléphone"
                    ])
                ]
            ])
            ->add('domain', TextType::class, [
                'label' => 'Domaine d\'activité',
                'attr' => ['maxlength' => 100],
                'constraints' => [
                    new Length([
                        'max' => 100,
                        'maxMessage' => '{{ limit }} caractères maximum autorisés',
                    ]),
                    new NotBlank([
                        "message" => "Vous devez renseigner un domaine d\'activité"
                    ])
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
