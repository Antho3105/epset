<?php

namespace App\Form;

use App\Entity\Candidate;
use App\Entity\Result;
use App\Entity\Survey;
use App\Repository\CandidateRepository;
use App\Repository\SurveyRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ResultType extends AbstractType
{
    private TokenStorageInterface $token;

    public function __construct(TokenStorageInterface $token)
    {
        $this->token = $token;
    }


    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('survey', EntityType::class, [
                'label' => 'Questionnaire',
                'class' => Survey::class,
                'query_builder' => function (SurveyRepository $surveyRepository) {
                    return $surveyRepository->createQueryBuilder('survey')
                        ->where('survey.user = :user')
                        ->setParameter('user', $this->token->getToken()->getUser())
                        ->orderBy('survey.id', 'ASC');
                },

            ])
            ->add('candidate',EntityType::class, [
                'label' => 'Candidat',
                'class' => Candidate::class,
                'query_builder' => function (CandidateRepository $candidateRepository) {
                    return $candidateRepository->createQueryBuilder('candidate')
                        ->where('candidate.user = :user')
                        ->setParameter('user', $this->token->getToken()->getUser())
                        ->orderBy('candidate.lastName', 'ASC');
                },
            ])



        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Result::class,
        ]);
    }
}
