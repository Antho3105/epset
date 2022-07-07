<?php

namespace App\Form;

use App\Entity\Survey;
use Doctrine\DBAL\Types\BooleanType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\LessThanOrEqual;
use Symfony\Component\Validator\Constraints\Range;

class SurveyType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('ref', TextType::class, [
                'label' => 'Référence',
                'attr' => [
                    'maxlength' => 15
                ],
                'constraints' => new Length([
                    'max' => 15,
                    'maxMessage' => 'Merci de saisir une référence de {{ limit }} caractères maximum',
                ])
            ])
            ->add('difficulty', IntegerType::class, [
                'label' => 'Niveau de difficulté (0-5)',
                'attr' => [
                    'min' => 0,
                    'max' => 5
                ],
                'constraints' => new Range([
                    'min' => 0,
                    'max' => 5,
                    'notInRangeMessage' => 'Merci de saisir un chiffre entre {{ min }} et {{ max }}',
                ])])
            ->add('questionTimer', IntegerType::class, [
                'label' => 'Délai par question (s)',
                'attr' => [
                    'min' => 0,
                    'max' => 250,
                ],
                'constraints' => new Range([
                    'min' => 0,
                    'max' => 250,
                    'notInRangeMessage' => 'Merci de saisir un chiffre entre {{ min }} et {{ max }} secondes',
                ])
            ])
            ->add('ordered', CheckboxType::class, [
                'label' => 'Affichage des questions dans l\'ordre de création',
                'required' => false
            ])
            ->add('detail', TextareaType::class,
                ['attr' => [
                    'class' => 'tinymceXL',
                    'maxlength' => 1500
                ],
                    'label' => 'Détails du questionnaire :',
                    'constraints' => new Length([
                        'max' => 1500,
                        'maxMessage' => 'Détails trop long'
                    ])
                ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Survey::class,
        ]);
    }
}
