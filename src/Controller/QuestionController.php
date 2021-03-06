<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Answer;
use App\Entity\Question;
use App\Entity\Survey;
use App\Form\AnswerType;
use App\Form\QuestionAnswerType;
use App\Form\QuestionType;
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
                throw new AccessDeniedHttpException();
        }

        $question = new Question();
        $form = $this->createForm(QuestionAnswerType::class, $question);
        $form->handleRequest($request);

        $errorMsg = null;
        if ($form->isSubmitted() && $form->isValid()) {
            if (!$question->getQuestion()) {
                $errorMsg = 'Merci de renseigner la question.';
            } elseif (!$rightAnswer = $form->get('answer')->getData()) {
                $errorMsg = 'Merci de renseigner la bonne réponse!';
            } elseif (!$answerChoice2 = $form->get('choice2')->getData()) {
                $errorMsg = 'Merci de renseigner le 2e choix de réponse.';
            } elseif (!$answerChoice3 = $form->get('choice3')->getData()) {
                $errorMsg = 'Merci de renseigner le 3e choix de réponse.';
            } elseif (!$answerChoice4 = $form->get('choice4')->getData()) {
                $errorMsg = 'Merci de renseigner le 4e choix de réponse.';
            } elseif (!$answerChoice5 = $form->get('choice5')->getData()) {
                $errorMsg = 'Merci de renseigner le 5e choix de réponse.';
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

            // Récupérer l'image liée à la question si elle existe
            $questionImg = $form->get("imgFileName")->getData();
            if ($questionImg) {
                // Générer le nom du fichier de destination.
                $imgFileName = 'img_question_' . $question->getId() . '.' . $questionImg->guessExtension();
                // Mettre à jour la question avec le nom du fichier image.
                $question->setImgFileName($imgFileName);
                // Persister la question pour sauvegarder le nom du fichier.
                $questionRepository->add($question, true);
                // Déplacer le fichier sur le serveur.
                $questionImg->move('./surveyQuestion_img/', $imgFileName);
            }

            $this->persistAnswer($rightAnswer, true, $question, $answerRepository);
            $this->persistAnswer($answerChoice2, false, $question, $answerRepository);
            $this->persistAnswer($answerChoice3, false, $question, $answerRepository);
            $this->persistAnswer($answerChoice4, false, $question, $answerRepository);
            $this->persistAnswer($answerChoice5, false, $question, $answerRepository);

            return $this->redirectToRoute('app_survey_show', ['id' => $survey->getId()], Response::HTTP_SEE_OTHER);
        }
        return $this->renderForm('question/newQuestion.html.twig', [
            'survey' => $survey,
            'question' => $question,
            'form' => $form,
        ]);
    }

    /**
     * Methode d'affichage d'une question.
     * @param Question $question
     * @param AnswerRepository $answerRepository
     * @return Response
     */
    #[Route('/{id}', name: 'app_question_show', methods: ['GET'])]
    public function show(Question $question, AnswerRepository $answerRepository): Response
    {
        // Si l'utilisateur n'est pas administrateur gérer l'accès.
        if (!$this->isGranted("ROLE_ADMIN")) {
            // si la question a été supprimée, générer une erreur.
            if ($question->getDeleteDate())
                throw new AccessDeniedHttpException();
            if ($this->isGranted("ROLE_CENTER")) {
                // Si la question passée en GET n'est pas lié à une formation du centre générer une erreur
                if ($question->getSurvey()->getCourse()->getUser() !== $this->getUser())
                    throw new AccessDeniedHttpException();
            }
            // Si la question passée en GET n'a pas été créée par le formateur générer une erreur
            if ($this->isGranted("ROLE_TRAINER")) {
                if ($question->getSurvey()->getUser() !== $this->getUser())
                    throw new AccessDeniedHttpException();
            }
        }
        // Récupérer les réponses.
        // Si admin les afficher meme si elles ont été supprimées.
        if ($this->isGranted("ROLE_ADMIN")) {
            $answers = $answerRepository->findBy([
                'question' => $question,
            ]);
        } else
            $answers = $answerRepository->findBy([
                'question' => $question,
                'deleteDate' => null
            ]);

        return $this->render('question/show.html.twig', [
            'question' => $question,
            'answers' => $answers
        ]);
    }

    /**
     * Méthode de modification d'une question.
     * Seuls les formateurs sont autorisés à modifier les questions.
     * @IsGranted("ROLE_TRAINER")
     *
     * @param Request $request
     * @param Question $question
     * @param QuestionRepository $questionRepository
     * @return Response
     */
    #[Route('/{id}/edit', name: 'app_question_edit', methods: ['GET', 'POST'])]
    public function questionEdit(Request $request, Question $question, QuestionRepository $questionRepository): Response
    {
        // Si l'utilisateur n'est pas administrateur gérer l'accès.
        if (!$this->isGranted("ROLE_ADMIN")) {
            // Si la question a été supprimée, générer une erreur.
            if ($question->getDeleteDate())
                throw new AccessDeniedHttpException();
            // Si la question passée en GET n'a pas été créée par le formateur, générer une erreur
            if ($question->getSurvey()->getUser() !== $this->getUser())
                throw new AccessDeniedHttpException();
        }

        $form = $this->createForm(QuestionType::class, $question);
        $form->handleRequest($request);
        if (($form->isSubmitted() && $form->isValid())) {
            $questionRepository->add($question, true);
            return $this->redirectToRoute('app_survey_show', ['id' => $question->getSurvey()->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('question/editQuestion.html.twig', [
            'question' => $question,
            'form' => $form,
        ]);
    }

    /**
     * Méthode de modification d'une réponse
     * Seuls les formateurs sont autorisés à modifier les questions.
     * @IsGranted("ROLE_TRAINER")
     *
     * @param Request $request
     * @param Answer $answer
     * @param AnswerRepository $answerRepository
     * @return Response
     */
    #[Route('/{id}/edit/answer', name: 'app_answer_edit', methods: ['GET', 'POST'])]
    public function answerEdit(Request $request, Answer $answer, AnswerRepository $answerRepository): Response
    {
        // Si l'utilisateur n'est pas administrateur gérer l'accès.
        if (!$this->isGranted("ROLE_ADMIN")) {
            // Si la réponse a été supprimée, générer une erreur.
            if ($answer->getDeleteDate())
                throw new AccessDeniedHttpException();
            // Si la réponse passée en GET n'a pas été créée par le formateur générer une erreur
            if ($this->isGranted("ROLE_TRAINER")) {
                if ($answer->getQuestion()->getSurvey()->getUser() !== $this->getUser())
                    throw new AccessDeniedHttpException();
            }
        }

        $form = $this->createForm(AnswerType::class, $answer);
        $form->handleRequest($request);

        if (($form->isSubmitted() && $form->isValid())) {
            $answerRepository->add($answer, true);
            return $this->redirectToRoute('app_question_show', ['id' => $answer->getQuestion()->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('question/editAnswer.html.twig', [
            'form' => $form,
        ]);
    }

    /**
     * Soft delete (seul l'administrateur peut supprimer définitivement l'élément).
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
        // Si l'utilisateur n'est pas administrateur gérer l'accès.
        if (!$this->isGranted("ROLE_ADMIN")) {
            if ($this->isGranted("ROLE_CENTER")) {
                // Les centres n'ont pas le droit de supprimer une question.
                throw new AccessDeniedHttpException();
            }
            // Si la question passée en GET n'a pas été créée par le formateur générer une erreur
            if ($this->isGranted("ROLE_TRAINER")) {
                if ($question->getSurvey()->getUser() !== $this->getUser())
                    throw new AccessDeniedHttpException();
            }
        }
        if ($this->isCsrfTokenValid('delete' . $question->getId(), $request->request->get('_token'))) {
            $answers = $question->getAnswers();
            foreach ($answers as $answer) {
                $answerRepository->softDelete($answer, true);
            }
            $questionRepository->softDelete($question, true);
        }
        $this->addFlash('alert', 'Question et réponses supprimées.');
        return $this->redirectToRoute('app_survey_show', ['id' => $question->getSurvey()->getId()], Response::HTTP_SEE_OTHER);
    }

    /**
     * Suppression complete d'une question et de ses réponses.
     * Seuls les administrateurs ont accès.
     * @IsGranted("ROLE_ADMIN")
     *
     * @param Request $request
     * @param Question $question
     * @param QuestionRepository $questionRepository
     * @param AnswerRepository $answerRepository
     * @return Response
     */
    #[Route('/{id}/hardDelete', name: 'app_question_hard_delete', methods: ['POST'])]
    public function hardDelete(Request $request, Question $question, QuestionRepository $questionRepository, AnswerRepository $answerRepository): Response
    {
        if ($this->isCsrfTokenValid('_tokenHardDelete' . $question->getId(), $request->request->get('_tokenHardDelete'))) {
            $answers = $question->getAnswers();
            foreach ($answers as $answer) {
                $answerRepository->remove($answer, true);
            }
            $questionRepository->remove($question, true);
        }
        $this->addFlash('alert', 'Question et réponses définitivement supprimées.');
        return $this->redirectToRoute('app_survey_show', ['id' => $question->getSurvey()->getId()], Response::HTTP_SEE_OTHER);
    }


    /**
     * Methode pour restaurer une question et ses réponses.
     * Seuls les administrateurs ont accès.
     * @IsGranted("ROLE_ADMIN")
     *
     */
    #[Route('/{id}/reset', name: 'app_question_reset', methods: ['POST'])]
    public function reset(Request $request, Question $question, QuestionRepository $questionRepository, AnswerRepository $answerRepository): Response
    {
        if ($this->isCsrfTokenValid('_tokenResetQuestion' . $question->getId(), $request->request->get('_tokenResetQuestion'))) {
            $answers = $question->getAnswers();

            foreach ($answers as $answer) {
                $answerRepository->reset($answer, true);
            }

            $questionRepository->reset($question, true);
            $this->addFlash('alert', 'Question et réponses restaurées !');

        }
        return $this->redirectToRoute('app_survey_index', [], Response::HTTP_SEE_OTHER);
    }


    /**
     * Methode privée de sauvegarde des réponses.
     *
     * @param string $candidateAnswer
     * @param bool $status
     * @param Question $question
     * @param AnswerRepository $answerRepository
     * @return void
     */
    private function persistAnswer(string $candidateAnswer, bool $status, Question $question, AnswerRepository $answerRepository): void
    {
        $answer = new Answer();
        $answer->setQuestion($question);
        $answer->setValue($candidateAnswer);
        $answer->setIsRightAnswer($status);
        $answerRepository->add($answer, true);
    }
}
