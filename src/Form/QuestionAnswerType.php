<?php

namespace App\Form;

use App\Entity\Question;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class QuestionAnswerType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('question', TextareaType::class)
            ->add('answer', TextareaType::class, ["mapped"=>false])
            ->add('choice2', TextareaType::class, ["mapped"=>false])
            ->add('choice3', TextareaType::class, ["mapped"=>false])
            ->add('choice4', TextareaType::class, ["mapped"=>false])
            ->add('choice5', TextareaType::class, ["mapped"=>false])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Question::class,
        ]);
    }
}
