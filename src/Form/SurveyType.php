<?php

namespace App\Form;

use App\Entity\Course;
use App\Entity\Survey;
use App\Repository\CourseRepository;
use App\Repository\VisibleCourseRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class SurveyType extends AbstractType
{
    private TokenStorageInterface $token;

    public function __construct(TokenStorageInterface $token)
    {
        $this->token = $token;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {


        $builder
            ->add('ref')
            ->add('detail')
            ->add('difficulty')
            ->add('questionTimer')
            ->add('ordered')
            // TODO debugger !!!!
            ->add('course', EntityType::class, [
                'label' => 'Formation',
                'class' => Course::class,
                'query_builder' => function (CourseRepository $courseRepository) {

                    $qb = $courseRepository->createQueryBuilder('course')
                        ->where('course.user = :user')
                        ->setParameter('user', $this->token->getToken()->getUser())
                        ->orderBy('course.id', 'ASC');
                },
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Survey::class,
        ]);
    }
}
