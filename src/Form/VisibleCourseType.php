<?php

namespace App\Form;

use App\Entity\Course;
use App\Entity\User;
use App\Entity\VisibleCourse;
use App\Repository\CourseRepository;
use App\Repository\UserRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class VisibleCourseType extends AbstractType
{
    private TokenStorageInterface $token;

    public function __construct(TokenStorageInterface $token)
    {
        $this->token = $token;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        $builder
            ->add('course',EntityType::class, [
                'label' => 'Formation',
                'class' => Course::class,
                'query_builder' => function (CourseRepository $courseRepository) {
                    return $courseRepository->createQueryBuilder('course')
                        ->where('course.user = :user')
                        ->setParameter('user', $this->token->getToken()->getUser())
                        ->orderBy('course.ref', 'ASC');
                },
            ])
            ->add('user', EntityType::class, [
                'label' => 'Formateur',
                'class' => User::class,
                'query_builder' => function (UserRepository $userRepository) {
                    return $userRepository->createQueryBuilder('user')
                        ->where('user.roles LIKE :roles')
                        ->setParameter('roles', '%"' . 'ROLE_TRAINER' . '"%')
                        ->orderBy('user.lastName', 'ASC');
                },]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => VisibleCourse::class,
        ]);
    }
}
