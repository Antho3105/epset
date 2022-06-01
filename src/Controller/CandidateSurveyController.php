<?php

namespace App\Controller;

use App\Form\UploadFile;
use App\Repository\AnswerRepository;
use App\Repository\CandidateRepository;
use App\Repository\QuestionRepository;
use App\Repository\ResultRepository;
use App\Repository\SurveyRepository;
use DateInterval;
use DateTime;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
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

        // Si le Questionnaire a déjà été commencé rediriger vers la fonction "question suivante".
        if ($result->getViewedQuestion() !== null)
            return $this->redirectToRoute('app_survey_end', [], Response::HTTP_SEE_OTHER);


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
                $cvFileName = 'CV_' . $result->getCandidate()->getLastName() . '_' . $result->getCandidate()->getFirstName() . '.' . $fileExtension;
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
                $coverLetterFilename = $survey->getRef() . ' Lettre_de_motivation_' . $result->getCandidate()->getLastName() . '_' . $result->getCandidate()->getFirstName() . '.' . $fileExtension;
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

                // Initialiser le tableau qui va contenir les id des questions.
                $questionList = [];
                // Récupérer les id et les ajouter au tableau.
                foreach ($questions as $question) {
                    $questionList[] = $question->getId();
                }
                // Si le questionnaire est aléatoire mélanger les questions.
                if (!$survey->isOrdered())
                    shuffle($questionList);

                // Mettre à jour la date du test pour contrôle du temps passé à la fin du questionnaire
                $result->setTestDate(new DateTime());

                $result->setQuestionList($questionList);
                $resultRepository->add($result, true);
            }


            // Rediriger vers la page de question.
            return $this->redirectToRoute('app_survey_next');
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
     * @throws \Exception
     */
    #[Route('/question/survey', name: 'app_survey_next', methods: ['GET', 'POST'])]
    public function next(Request $request, QuestionRepository $questionRepository, AnswerRepository $answerRepository, ResultRepository $resultRepository, MailerInterface $mailer): Response
    {
        // Récupérer le token dans la session.
        try {
            $token = $this->getTokenFromSession();
        } catch (NotFoundExceptionInterface $e) {
            throw $this->createNotFoundException();
        } catch (ContainerExceptionInterface $e) {
            throw throw new AccessDeniedHttpException();
        }
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

        if ($result->getViewedQuestion() !== null) {
            // Récupérer la réponse du candidat
            $answerId = (int)$request->get('candidateAnswer');

            if ($answerId)
                $result->setAnsweredQuestion($result->getAnsweredQuestion() + 1);
            else
                $result->setAnsweredQuestion($result->getAnsweredQuestion() + 0);

            // Si la liste de question est vide (en cas de nouveau clic sur le lien unique après la fin du test)
            if (count($questionList) === 0) {
                return $this->redirectToRoute('app_survey_end', [], Response::HTTP_SEE_OTHER);
            }

            // Calculer le résultat :
            $question = $questionRepository->find($questionList[0]);
            $rightAnswer = $answerRepository->findOneBy([
                'question' => $question,
                'isRightAnswer' => true,
                'deleteDate' => null
            ]);

            if ($rightAnswer->getId() === $answerId) {
                $result->setScore($result->getScore() + 1);
            } else
                $result->setScore($result->getScore() + 0);

            // Supprimer la première question.
            // TODO update repo pour Test
            array_shift($questionList);
            $result->setQuestionList($questionList);
            $resultRepository->add($result, true);

            // Si la liste de question est vide (fin du test) calculer le score final et rediriger vers la page fin.
            if (count($questionList) === 0) {
                $questionNb = count($questionRepository->findBy([
                    'Survey' => $result->getSurvey(),
                    'deleteDate' => null,
                ]));

                // Stocker le temps passé pour répondre aux questions et si le candidat a triché.
                $maxEndOfTest = new DateTime($result->getTestDate()->format("Y-m-d H:i:s"));
                $testTime = $questionNb * $result->getSurvey()->getQuestionTimer() * 1.2;
                $maxEndOfTest->add(new DateInterval('PT' . $testTime . 'S'));

                $now = new DateTime();

                $result->setTestDuration(date_diff($result->getTestDate(), $now));
                $gap = date_diff($now, $maxEndOfTest);
                if ($gap->invert === 1) {
                    $result->setIsCheater(1);
                } else {
                    $result->setIsCheater(0);
                }
//                $interval = $testDuration->format("%H:%I:%S");

                $finalScore = number_format($result->getScore() * 100 / $questionNb, 1, '.', ' ');
                $result->setFinalScore($finalScore);
                $resultRepository->add($result, true);

                // envoyer un mail au centre pour l'informer du résultat.
                $email = (new TemplatedEmail())
                    ->from(new Address($_ENV['ADMIN_EMAIL'], 'epset mailer'))
                    ->to($result->getCandidate()->getUser()->getEmail())
                    ->subject($result->getCandidate() . ' vient de terminer le test "' . $result->getSurvey() . '".')
                    ->htmlTemplate('mailer/email_center_end_of_test.html.twig')
                    ->context([
                        'result' => $result
                    ]);

                $mailer->send($email);

                return $this->redirectToRoute('app_survey_end', [], Response::HTTP_SEE_OTHER);
            }
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

        $candidate = $result->getCandidate();
        $survey = $result->getSurvey();

        // Mettre à jour le nombre de questions vues.
        $result->setViewedQuestion($result->getViewedQuestion() + 1);
        $resultRepository->add($result, true);

        // Rediriger vers la premiere question du questionnaire.
        return $this->render('candidateSurvey/question.html.twig', [
            'candidate' => $candidate,
            'survey' => $survey,
            'question' => $currentQuestion,
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

    #[Route('/end/survey', name: 'app_survey_end', methods: ['GET'])]
    public function end(Request $request, QuestionRepository $questionRepository, AnswerRepository $answerRepository, ResultRepository $resultRepository, SurveyRepository $surveyRepository): Response
    {
        // Récupérer le token dans la session.
        try {
            $token = $this->getTokenFromSession();
        } catch (NotFoundExceptionInterface $e) {
            throw $this->createNotFoundException();
        } catch (ContainerExceptionInterface $e) {
            throw throw new AccessDeniedHttpException();

        }
        // S'il n'y a pas de token en session générer une erreur.
        if ($token === null) {
            throw throw new AccessDeniedHttpException();
        }
        // Récupérer la fiche de résultat à partir du token.
        $result = $resultRepository->findOneBy([
            'token' => $token
        ]);

        $candidate = $result->getCandidate();
        $survey = $result->getSurvey();

        return $this->render('candidateSurvey/end.html.twig', [
            'result' => $result,
            'candidate' => $candidate,
            'survey' => $survey,
        ]);

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
