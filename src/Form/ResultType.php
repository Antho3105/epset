<?php

namespace App\Form;

use App\Entity\Candidate;
use App\Entity\Result;
use App\Entity\Survey;
use Doctrine\DBAL\Types\ArrayType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ResultType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        $builder
            ->add('candidate',EntityType::class, [
                'label' => 'Candidat',
                'class' => Candidate::class
            ])
            ->add('survey',EntityType::class, [
                'label' => 'Questionnaire',
                'class' => Survey::class
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Result::class,
        ]);
    }
}
