<?php

namespace App\Form;

use App\Entity\Candidate;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;

class CandidateType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // TODO ajouter des contraintes (nb de caractères...)
            ->add('lastName', TextType::class, [
                'label' => 'Nom',
                'constraints' => new Length([
                    'max' => 40,
                    'maxMessage' => '{{ limit }} caractères maximum autorisés',
                ])
            ])
            ->add('firstName', TextType::class, [
                'label' => 'Prénom',
                'constraints' => new Length([
                    'max' => 30,
                    'maxMessage' => '{{ limit }} caractères maximum autorisés',
                ])
                ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'constraints' => new Length([
                    'max' => 60,
                    'maxMessage' => '{{ limit }} caractères maximum autorisés',
                ])
                ])
            ->add('phone', TelType::class, [
                'label' => 'Téléphone',
                'constraints' => new Length([
                    'max' => 25,
                    'maxMessage' => '{{ limit }} caractères maximum autorisés',
                ])
                ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Candidate::class,
        ]);
    }
}
