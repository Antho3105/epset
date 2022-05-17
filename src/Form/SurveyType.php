<?php

namespace App\Form;

use App\Entity\Survey;
use Doctrine\DBAL\Types\BooleanType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SurveyType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // TODO ajouter des contraintes
            ->add('ref', TextType::class, ['label' => 'Référence'])
            ->add('detail', TextType::class, ['label' => 'Détails'])
            ->add('difficulty', IntegerType::class,['label' => 'Niveau de difficulté'])
            ->add('questionTimer', IntegerType::class, ['label' => 'Délai par question'])
            ->add('ordered', CheckboxType::class, ['label' => 'Affichage des questions dans l\'ordre de création']);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Survey::class,
        ]);
    }
}
