<?php

namespace App\Form;

use App\Entity\Answer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;

class AnswerType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('value', TextareaType::class, [
                'attr' => [
                    'class' => 'tinymceXS',
                    'maxlength' => 110
                ],
                'label' => false,
                'required' => false,
                'constraints' => new Length([
                    'max' => 200,
                    'maxMessage' => 'Réponse trop longue !'
                ])
            ]);
//        ->add('isRightAnswer', HiddenType::class, [
//            'required' => false
//        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Answer::class,
        ]);
    }
}
