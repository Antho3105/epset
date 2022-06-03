<?php

namespace App\Form;

use App\Entity\Question;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Length;

class QuestionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('question', TextareaType::class,
                ['attr' => [
                    'class' => 'tinymceL',
                    'maxlength' => 850
                ],
                    'label' => 'Question :',
                    'required' => false,
                    'constraints' => new Length([
                        'max' => 1000,
                        'maxMessage' => 'Question trop longue !'
                    ])
                ]
            )
            ->add('imgFileName', FileType::class, [
                'mapped' => false,
                'label' => false,
                'multiple' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '5360k',
                        'maxSizeMessage' => 'Image trop volumineuse',
                        'mimeTypes' => ['image/jpeg'],
                        'mimeTypesMessage' => 'Merci de charger une image valide'
                    ])
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Question::class,
        ]);
    }
}
