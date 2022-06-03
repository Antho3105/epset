<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;

class ContactType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom prénom',
                'attr' => ['maxlength' => 80],
                'constraints' => new Length([
                    'max' => 40,
                    'maxMessage' => '{{ limit }} caractères maximum autorisés',
                ])
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'attr' => ['maxlength' => 60],
                'constraints' => new Length([
                    'max' => 60,
                    'maxMessage' => '{{ limit }} caractères maximum autorisés',
                ])
            ])
            ->add('message', TextareaType::class, [
                'label' => 'Votre message',
                'attr' => [
                    'rows' => 6,
                    'maxlength' => 1500
                ],
                'constraints' => new Length([
                    'max' => 1500,
                    'maxMessage' => '{{ limit }} caractères maximum autorisés',
                ])
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
        ]);
    }
}