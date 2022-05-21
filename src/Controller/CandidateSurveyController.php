<?php

namespace App\Controller;

use App\Entity\Answer;
use App\Form\UploadFile;
use App\Repository\AnswerRepository;
use App\Repository\CandidateRepository;
use App\Repository\QuestionRepository;
use App\Repository\ResultRepository;
use App\Repository\SurveyRepository;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Annotation\Route;

class CandidateSurveyController extends AbstractController
{

    /**
     * Methode d'initialisation d'un questionnaire :
     *
     * @param Request $request
     * @param QuestionRepository $questionRepository
     * @param ResultRepository $resultRepository
     * @param CandidateRepository $candidateRepository
     * @param AnswerRepository $answerRepository
     * @param SurveyRepository $surveyRepository
     * @param string|null $token
     * @return Response
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('init/survey/{token}', name: 'app_survey_init', methods: ['GET', 'POST'])]
    public function begin(Request $request, QuestionRepository $questionRepository, ResultRepository $resultRepository, CandidateRepository $candidateRepository, AnswerRepository $answerRepository, SurveyRepository $surveyRepository, string $token = null): Response
    {
        // Récupérer le token passé en GET et le stocker dans la session.
        if ($token) {
            $this->getSessionService();
            $this->storeTokenInSession($token);
            // Rediriger vers la route sans le token pour ne pas le laisser visible.
            return $this->redirectToRoute('app_survey_init');
        };

        // Récupérer le token dans la session.
        $token = $this->getTokenFromSession();

        // S'il n'y a pas de token en session générer une erreur.
        if ($token === null) {
            throw throw new AccessDeniedHttpException();
        }

        // Récupérer la fiche de résultat à partir du token.
        $result = $resultRepository->findOneBy([
            'token' => $token
        ]);

        // Récupérer le questionnaire à partir de la fiche de résultat.
        $survey = $result->getSurvey();

        // Créer le formulaire pour l'upload des fichiers (CV et lettre de motivation).
        $form = $this->createForm(UploadFile::class,);
        $form->handleRequest($request);

        // Si le formulaire est valide uploader les fichiers sur le serveur et mettre à jour les entités sur la DB et démarrer le questionnaire.
        if ($form->isSubmitted() && $form->isValid()) {
            // Récupérer la fiche du candidat.
            $candidate = $result->getCandidate();
            // Récupérer le fichier CV en post.
            $cv = $form->get("CV_file")->getData();
            if ($cv) {
                // Récupérer l'extension du fichier
                $fileExtension = $cv->guessExtension();
                // Générer le non du fichier de destination.
                $cvFileName = 'CV ' . $result->getCandidate()->getLastName() . ' ' . $result->getCandidate()->getFirstName() . '.' . $fileExtension;
                // Mettre a jour le nom du CV sur la fiche du candidat.
                $candidate->setCvFileName($cvFileName);
                // Persister la fiche du candidat pour sauvegarder le nom du fichier.
                $candidateRepository->add($candidate, true);
                // Déplacer le fichier sur le serveur.
                $cv->move('./cv_files/', $cvFileName);
            }
            // Récupérer le fichier de lettre de motivation en post.
            $motivationLetter = $form->get("coverLetter")->getData();
            if ($motivationLetter) {
                // Récupérer l'extension du fichier
                $fileExtension = $motivationLetter->guessExtension();
                // Générer le non du fichier de destination.
                $coverLetterFilename = $survey->getRef() . ' Lettre de motivation ' . $result->getCandidate()->getLastName() . ' ' . $result->getCandidate()->getFirstName() . '.' . $fileExtension;
                // Mettre à jour le nom de la lettre de motivation sur la fiche de résultat.
                $result->setCoverLetterFilename($coverLetterFilename);
                // Déplacer le fichier sur le serveur.
                $motivationLetter->move('./coverLetters_files/', $coverLetterFilename);
            }

            // Si le tableau de question est deja renseigné ne pas le recharger (lors d'un rechargement de page pas ex)
            if (!$result->getQuestionList()) {
                // Récupérer la liste de question du questionnaire.
                $questions = $questionRepository->findBy([
                    'Survey' => $survey,
                    'deleteDate' => null
                ],
                    [
                        'id' => 'ASC'
                    ]
                );

                // Initialiser le tableau qui va contenir les id des questions (et si elles ont été lues).
                $questionList = [];
                // Récupérer les id et les ajouter au tableau en initialisant à false (non lu)
                foreach ($questions as $question) {
                    $questionList[] = $question->getId();
                }
                // Si le questionnaire est aléatoire mélanger les questions.
                if (!$survey->isOrdered())
                    shuffle($questionList);
                $result->setQuestionList($questionList);
                $resultRepository->add($result, true);
            } else
                $questionList = $result->getQuestionList();

            // Récupérer la première question.
            $currentQuestion = $questionRepository->find($questionList[0]);

            // Récupérer la question et les réponses (en les mélangeant)
            $question = $currentQuestion->getQuestion();

            // Récupérer la liste des réponses.
            $answers = $answerRepository->findBy([
                'question' => $currentQuestion,
                'deleteDate' => null],
                [
                    'id' => 'ASC'
                ]);
            shuffle($answers);

            // Rediriger vers la premiere question du questionnaire.
            return $this->render('candidateSurvey/question.html.twig', [
                'candidate' => $candidate,
                'survey' => $survey,
                'question' => $question,
                'answers' => $answers
            ]);
        }

        return $this->renderForm('candidateSurvey/init.html.twig', [
            'survey' => $survey,
            'result' => $result,
            'form' => $form
        ]);
    }

    /**
     *
     *
     */
    #[Route('/question/survey', name: 'app_survey_next', methods: ['GET', 'POST'])]
    public function next(Request $request, QuestionRepository $questionRepository, AnswerRepository $answerRepository, ResultRepository $resultRepository, SurveyRepository $surveyRepository): Response
    {
        // Récupérer le token dans la session.
        $token = $this->getTokenFromSession();

        // S'il n'y a pas de token en session générer une erreur.
        if ($token === null) {
            throw throw new AccessDeniedHttpException();
        }

        // Récupérer la fiche de résultat à partir du token.
        $result = $resultRepository->findOneBy([
            'token' => $token
        ]);

        // Récupérer la liste des questions
        $questionList = $result->getQuestionList();

        if (count($questionList) === 0) {
            throw $this->createNotFoundException();
        }

        // Récupérer la réponse du candidat
        $answerId = (int)$request->get('candidateAnswer');

        // calculer le résultat :
        $question = $questionRepository->find($questionList[0]);
        $rightAnswer = $answerRepository->findBy([
            'question' => $question,
            'isRightAnswer' => true
        ])[0];

        if ($rightAnswer->getId() === $answerId) {
            $result->setScore($result->getScore() + 1);
        }
        $result->setAnsweredQuestion($result->getAnsweredQuestion() + 1);

        // Supprimer la première question.
        array_shift($questionList);
        $result->setQuestionList($questionList);
        $resultRepository->add($result, true);

        if (count($questionList) === 0) {
            // TODO renvoyer vers une page de fin de test (et afficher le score.
            dd('fin du test');
        }


        // Récupérer la première question.
        $currentQuestion = $questionRepository->find($questionList[0]);

        // Récupérer la liste des réponses.
        $answers = $answerRepository->findBy([
            'question' => $currentQuestion,
            'deleteDate' => null],
            [
                'id' => 'ASC'
            ]);
        shuffle($answers);

        $question = $currentQuestion->getQuestion();
        $candidate = $result->getCandidate();
        $survey = $result->getSurvey();


        // Rediriger vers la premiere question du questionnaire.
        return $this->render('candidateSurvey/question.html.twig', [
            'candidate' => $candidate,
            'survey' => $survey,
            'question' => $question,
            'answers' => $answers
        ]);


    }


    /**
     * Stocke le token dans la session.
     *
     * @return SessionInterface
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function getSessionService(): SessionInterface
    {
        /** @var Request $request */
        $request = $this->container->get('request_stack')->getCurrentRequest();

        return $request->getSession();
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function getTokenFromSession(): ?string
    {
        return $this->getSessionService()->get('accessToken');
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function storeTokenInSession(string $token): void
    {
        $this->getSessionService()->set('accessToken', $token);
    }
}
