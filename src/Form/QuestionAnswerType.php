<?php

namespace App\Form;

use App\Entity\Question;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;

class QuestionAnswerType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('question', TextareaType::class,
                ['attr' => [
                    'class' => 'tinymce',
                    'maxlength' => 1000
                ],
                    'label' => 'Question :',
                    'required' => false,
                    'constraints' => new Length([
                        'max' => 1000,
                        'maxMessage' => 'Question trop longue !'
                    ])
                ]
            )
            ->add('answer', TextareaType::class, [
                'mapped'=> false,
                'attr' => [
                    'class' => 'tinymce',
                    'maxlength' => 200
                ],
                'label' => 'Bonne réponse :',
                'required' => false,
                'constraints' => new Length([
                    'max' => 200,
                    'maxMessage' => 'Réponse trop longue !'
                ])
            ])
            ->add('choice2', TextareaType::class, [
                'mapped'=> false,
                'attr' => [
                    'class' => 'tinymce',
                    'maxlength' => 200
                ],
                'label' => '2nd choix de réponse :',
                'required' => false,
                'constraints' => new Length([
                    'max' => 200,
                    'maxMessage' => 'Réponse trop longue !'
                ])
            ])
            ->add('choice3', TextareaType::class, [
                'mapped'=> false,
                'attr' => [
                    'class' => 'tinymce',
                    'maxlength' => 200
                ],
                'label' => '3e choix de réponse :',
                'required' => false,
                'constraints' => new Length([
                    'max' => 200,
                    'maxMessage' => 'Réponse trop longue !'
                ])
            ])
            ->add('choice4', TextareaType::class, [
                'mapped'=> false,
                'attr' => [
                    'class' => 'tinymce',
                    'maxlength' => 200
                ],
                'label' => '4e choix de réponse :',
                'required' => false,
                'constraints' => new Length([
                    'max' => 200,
                    'maxMessage' => 'Réponse trop longue !'
                ])
            ])
            ->add('choice5', TextareaType::class, [
                'mapped'=> false,
                'attr' => [
                    'class' => 'tinymce',
                    'maxlength' => 200
                ],
                'label' => '5e choix de réponse :',
                'required' => false,
                'constraints' => new Length([
                    'max' => 200,
                    'maxMessage' => 'Réponse trop longue !'
                ])
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Question::class,
        ]);
    }
}
