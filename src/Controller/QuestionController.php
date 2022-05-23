<?php

namespace App\Controller;

use App\Entity\Answer;
use App\Entity\Question;
use App\Entity\Survey;
use App\Form\QuestionAnswerType;
use App\Repository\AnswerRepository;
use App\Repository\QuestionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @IsGranted("ROLE_USER")
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

    /**
     * Seuls les formateurs ont les droits pour créer une nouvelle question.
     * @IsGranted("ROLE_TRAINER")
     *
     * @param Request $request
     * @param Survey $survey
     * @param QuestionRepository $questionRepository
     * @param AnswerRepository $answerRepository
     * @return Response
     */
    #[Route('/new/{id}', name: 'app_question_new', methods: ['GET', 'POST'])]
    public function new(Request $request, Survey $survey, QuestionRepository $questionRepository, AnswerRepository $answerRepository): Response
    {
        // Si l'utilisateur n'est pas administrateur gérer l'accès.
        if (!$this->isGranted("ROLE_ADMIN")) {
            // Si le questionnaire passé en GET n'a pas été créé par le formateur générer une erreur
            if ($survey->getUser() !== $this->getUser())
                throw throw new AccessDeniedHttpException();
        }

        $question = new Question();
        $form = $this->createForm(QuestionAnswerType::class, $question);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $errorMsg = null;
            // TODO passer les messages en constante de l'appli.
            if (!$question->getQuestion()) {
                $errorMsg = 'Merci de renseigner la question !';
            } elseif (!$rightAnswer = $form->get('answer')->getData()) {
                $errorMsg = 'Merci de renseigner la bonne réponse !';
            } elseif (!$answerChoice2 = $form->get('choice2')->getData()) {
                $errorMsg = 'Merci de renseigner le 2e choix de réponse !';
            } elseif (!$answerChoice3 = $form->get('choice3')->getData()) {
                $errorMsg = 'Merci de renseigner le 3e choix de réponse !';
            } elseif (!$answerChoice4 = $form->get('choice4')->getData()) {
                $errorMsg = 'Merci de renseigner le 4e choix de réponse !';
            } elseif (!$answerChoice5 = $form->get('choice5')->getData()) {
                $errorMsg = 'Merci de renseigner le 5e choix de réponse !';
            }

            if ($errorMsg) {
                $this->addFlash('alert', $errorMsg);
                return $this->renderForm('question/newQuestion.html.twig', [
                    'question' => $question,
                    'form' => $form,
                ]);
            }

            $question->setSurvey($survey);
            $questionRepository->add($question, true);

            $answer = new Answer();
            $answer->setQuestion($question);
            $answer->setValue($rightAnswer);
            $answer->setIsRightAnswer(true);
            $answerRepository->add($answer, true);

            $answer2 = new Answer();
            $answer2->setQuestion($question);
            $answer2->setValue($answerChoice2);
            $answer2->setIsRightAnswer(false);
            $answerRepository->add($answer2, true);

            $answer3 = new Answer();
            $answer3->setQuestion($question);
            $answer3->setValue($answerChoice3);
            $answer3->setIsRightAnswer(false);
            $answerRepository->add($answer3, true);

            $answer4 = new Answer();
            $answer4->setQuestion($question);
            $answer4->setValue($answerChoice4);
            $answer4->setIsRightAnswer(false);
            $answerRepository->add($answer4, true);

            $answer5 = new Answer();
            $answer5->setQuestion($question);
            $answer5->setValue($answerChoice5);
            $answer5->setIsRightAnswer(false);
            $answerRepository->add($answer5, true);

            return $this->redirectToRoute('app_survey_show', ['id' => $survey->getId()], Response::HTTP_SEE_OTHER);
        }
        return $this->renderForm('question/newQuestion.html.twig', [
            'question' => $question,
            'form' => $form,
        ]);
    }


//-------------------- Ancien version de conception --------------------------------------------//

//    #[Route('/new/{id}', name: 'app_question_new', methods: ['GET', 'POST'])]
//    public function new(Request $request, Survey $survey, QuestionRepository $questionRepository): Response
//    {
//        // Si l'utilisateur n'est pas administrateur gérer l'accès.
//        if (!$this->isGranted("ROLE_ADMIN")) {
//            // Si le questionnaire passé en GET n'a pas été créé par le formateur générer une erreur
//            if ($survey->getUser() !== $this->getUser())
//                throw throw new AccessDeniedHttpException();
//        }
//        $question = new Question();
//        $form = $this->createForm(QuestionType::class, $question);
//        $form->handleRequest($request);
//
//        if ($form->isSubmitted() && $form->isValid()) {
//
//
//            $question->setSurvey($survey);
//            $questionRepository->add($question, true);
//
//            return $this->redirectToRoute('app_survey_show', ['id' => $survey->getId()], Response::HTTP_SEE_OTHER);
//        }
//
//        return $this->renderForm('question/new.html.twig', [
//            'question' => $question,
//            'form' => $form,
//        ]);
//    }
//

    #[Route('/{id}', name: 'app_question_show', methods: ['GET'])]
    public function show(Question $question, AnswerRepository $answerRepository): Response
    {
        // TODO Sécuriser l'accès aux page de detail de question

        if ($question->getDeleteDate())
            throw throw new AccessDeniedHttpException();


        // Récupérer les réponses.
        $answers = $answerRepository->findBy([
            'question' => $question,
            'deleteDate' => null
        ]);

        return $this->render('question/show.html.twig', [
            'question' => $question,
            'answers' => $answers

        ]);
    }


    #[Route('/{id}/edit', name: 'app_question_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Question $question, QuestionRepository $questionRepository, AnswerRepository $answerRepository): Response
    {
        $form = $this->createForm(QuestionAnswerType::class, $question);
        $answers = $answerRepository->findBy([
            'question' => $question,
            'deleteDate' => null
        ], [
            'id' => 'ASC'
        ]);


        $answer = $answers[0];
        $choice2 = $answers[1];
        $choice3 = $answers[2];
        $choice4 = $answers[3];
        $choice5 = $answers[4];

        // TODO débugger l'éditeur de réponse.
        dd($answers);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $questionRepository->add($question, true);

            return $this->redirectToRoute('app_survey_show', ['id' => $question->getSurvey()->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('question/edit.html.twig', [
            'question' => $question,
            'answer' => $answer,
            'choice2' => $choice2,
            'choice3' => $choice3,
            'choice4' => $choice4,
            'choice5' => $choice5,
            'form' => $form,
        ]);
    }

    /**
     * Soft delete (seul l'administrateur peux supprimer définitivement l'élément
     *
     * @param Request $request
     * @param Question $question
     * @param QuestionRepository $questionRepository
     * @param AnswerRepository $answerRepository
     * @return Response
     */
    #[Route('/{id}', name: 'app_question_delete', methods: ['POST'])]
    public function delete(Request $request, Question $question, QuestionRepository $questionRepository, AnswerRepository $answerRepository): Response
    {
        if ($this->isCsrfTokenValid('delete' . $question->getId(), $request->request->get('_token'))) {
            $answers = $question->getAnswers();
            foreach ($answers as $answer) {
                $answerRepository->softDelete($answer, true);
            }
            $questionRepository->softDelete($question, true);
        }

        return $this->redirectToRoute('app_survey_show', ['id' => $question->getSurvey()->getId()], Response::HTTP_SEE_OTHER);
    }
}
