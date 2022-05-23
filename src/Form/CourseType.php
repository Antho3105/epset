<?php

namespace App\Form;

use App\Entity\Course;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;

class CourseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre de la formation',
                'constraints' => new Length([
                    'max' => 100,
                    'maxMessage' => 'Merci de saisir une titre de {{ limit }} caractères maximum',
                ])
            ])
            ->add('ref', TextType::class, [
                'label' => 'Référence :',
                'constraints' => new Length([
                    'max' => 15,
                    'maxMessage' => 'Merci de saisir une référence de {{ limit }} caractères maximum',
                ])
            ])
            ->add('level', TextType::class, [
                'label' => 'Niveau :',
                'constraints' => new Length([
                    'max' => 40,
                    'maxMessage' => 'Merci de saisir une niveau de {{ limit }} caractères maximum',
                ])
            ])
            ->add('detail', TextareaType::class,
                ['attr' => [
                    'class' => 'tinymce',
                    'maxlength' => 1500],
                    'label' => 'Détails de la formation :',
                    'required' => false,
                    'constraints' => new Length([
                        'max' => 1500,
                        'maxMessage' => 'Détails trop long',
                    ])
                ]);

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Course::class,
        ]);
    }
}
