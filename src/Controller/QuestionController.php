<?php

namespace App\Controller;

use App\Entity\Question;
use App\Entity\Survey;
use App\Form\QuestionType;
use App\Repository\QuestionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @IsGranted("ROLE_TRAINER")
 */
#[Route('/question')]
class QuestionController extends AbstractController
{
//    #[Route('/', name: 'app_question_index', methods: ['GET'])]
//    public function index(QuestionRepository $questionRepository): Response
//    {
//        return $this->render('question/index.html.twig', [
//            'questions' => $questionRepository->findAll(),
//        ]);
//    }

    #[Route('/new/{id}', name: 'app_question_new', methods: ['GET', 'POST'])]
    public function new(Request $request, Survey $survey, QuestionRepository $questionRepository): Response
    {
        // Si l'utilisateur n'est pas administrateur gérer l'accès.
        if (!$this->isGranted("ROLE_ADMIN")) {
            // Si le questionnaire passé en GET n'a pas été créé par le formateur générer une erreur
            if ($survey->getUser() !== $this->getUser())
                throw throw new AccessDeniedHttpException();
        }
        $question = new Question();
        $form = $this->createForm(QuestionType::class, $question);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $question->setSurvey($survey);
            $questionRepository->add($question, true);

            return $this->redirectToRoute('app_survey_show', ['id' => $survey->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('question/new.html.twig', [
            'question' => $question,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_question_show', methods: ['GET'])]
    public function show(Question $question): Response
    {
        return $this->render('question/show.html.twig', [
            'question' => $question,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_question_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Question $question, QuestionRepository $questionRepository): Response
    {
        $form = $this->createForm(QuestionType::class, $question);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $questionRepository->add($question, true);

            return $this->redirectToRoute('app_survey_show', ['id' => $question->getSurvey()->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('question/edit.html.twig', [
            'question' => $question,
            'form' => $form,
        ]);
    }

    /**
     * Soft delete (seul l'administrateur peux supprimer définitivement l'element
     *
     * @param Request $request
     * @param Question $question
     * @param QuestionRepository $questionRepository
     * @return Response
     */
    #[Route('/{id}', name: 'app_question_delete', methods: ['POST'])]
    public function delete(Request $request, Question $question, QuestionRepository $questionRepository): Response
    {
        if ($this->isCsrfTokenValid('delete' . $question->getId(), $request->request->get('_token'))) {
            $questionRepository->softDelete($question, true);
        }

        return $this->redirectToRoute('app_survey_show', ['id' => $question->getSurvey()->getId()], Response::HTTP_SEE_OTHER);
    }
}
