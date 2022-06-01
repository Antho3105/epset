<?php

namespace App\Controller;


use App\Entity\Result;
use App\Repository\CandidateRepository;
use App\Repository\CourseRepository;
use App\Repository\QuestionRepository;
use App\Repository\ResultRepository;
use App\Repository\SurveyRepository;
use DateInterval;
use DateTime;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 *
 * @IsGranted("ROLE_CENTER")
 *
 */
#[Route('/result')]
class ResultController extends AbstractController
{
    #[Route('/', name: 'app_result_index', methods: ['GET'])]
    public function index(ResultRepository $resultRepository, CandidateRepository $candidateRepository): Response
    {
        // Si l'utilisateur est administrateur afficher tous les résultats.
        if ($this->isGranted("ROLE_ADMIN")) {
            return $this->render('result/index.html.twig', [
                'results' => $resultRepository->findAll(),
            ]);
        }
        // Si l'utilisateur est un centre n'afficher que les résultats des candidats qui lui sont liés et non supprimés.
        if ($this->isGranted("ROLE_CENTER")) {
            // Récupérer tous les candidats liés au centre.
            $candidates = $candidateRepository->findBy([
                'user' => $this->getUser(),
                'deleteDate' => null
            ]);
            // initialiser le tableau des résultats
            $results = [];
            // Rechercher les résultats de chaque candidat.
            foreach ($candidates as $candidate) {
                $resultsByCandidate = $resultRepository->findBy([
                    'candidate' => $candidate,
                    'deleteDate' => null
                ]);
                  // Pour chaque résultat d'un candidat ajouter l'ajouter à la liste à afficher.
                foreach ($resultsByCandidate as $resultByCandidate) {
                    $results[] = $resultByCandidate;
                }
            }
            return $this->render('result/index.html.twig', [
                'results' => $results,
            ]);
        }
        return $this->render('main/index.html.twig');
    }

    /**
     *
     * Methode permettant d'afficher la page de choix candidat / formation.
     *
     * @param Request $request
     * @param ResultRepository $resultRepository
     * @param CandidateRepository $candidateRepository
     * @param CourseRepository $courseRepository
     * @return Response
     */
    #[Route('/new', name: 'app_result_new', methods: ['GET', 'POST'])]
    public function new(Request $request, ResultRepository $resultRepository, CandidateRepository $candidateRepository, CourseRepository $courseRepository): Response
    {
        $user = $this->getUser();
        // Récupérer tous les candidats (non supprimés) du centre.
        $candidates = $candidateRepository->findBy([
            'user' => $user,
            'deleteDate' => null
        ]);
        // Initialiser le tableau de tous les questionnaires du centre.
        $surveys = [];
        // Récupérer toutes les formations (non supprimées) du centre
        $courses = $courseRepository->findBy([
            'user' => $user,
            'deleteDate' => null
        ]);
        // Pour chaque formation, ajouter tous les questionnaires liés et non supprimés au tableau.
        foreach ($courses as $course) {
            $courseSurveys = $course->getSurveys();
            foreach ($courseSurveys as $courseSurvey) {
                if ($courseSurvey->getDeleteDate() === null)
                    $surveys[] = $courseSurvey;
            }
        }

        // rendre la vue avec les tableaux.
        return $this->render('result/new.html.twig', [
            'candidates' => $candidates,
            'surveys' => $surveys
        ]);

    }

    /**
     * Methode permettant de créer une fiche de résultat et d'envoyer un mail au candidat
     *
     * @param MailerInterface $mailer
     * @param Request $request
     * @param ResultRepository $resultRepository
     * @param CandidateRepository $candidateRepository
     * @param SurveyRepository $surveyRepository
     * @param CourseRepository $courseRepository
     * @return Response
     * @throws TransportExceptionInterface
     */
    #[Route('/add', name: 'app_result_add', methods: ['GET', 'POST'])]
    public function add(MailerInterface $mailer, Request $request, ResultRepository $resultRepository, CandidateRepository $candidateRepository, SurveyRepository $surveyRepository, CourseRepository $courseRepository): Response
    {
        $candidate = $candidateRepository->find((int)$request->get('candidate'));
        $survey = $surveyRepository->find((int)$request->get('survey'));

        // Verifier que les entités passés en get appartiennent au centre.
        $user = $this->getUser();
        // Récupérer tous les candidats (non supprimés) du centre.
        $candidates = $candidateRepository->findBy([
            'user' => $user,
            'deleteDate' => null
        ]);
        // Initialiser le tableau de tous les questionnaires du centre.
        $surveys = [];
        // Récupérer toutes les formations (non supprimées) du centre
        $courses = $courseRepository->findBy([
            'user' => $user,
            'deleteDate' => null
        ]);
        // Pour chaque formation, ajouter tous les questionnaires liés et non supprimés au tableau.
        foreach ($courses as $course) {
            $courseSurveys = $course->getSurveys();
            foreach ($courseSurveys as $courseSurvey) {
                if ($courseSurvey->getDeleteDate() === null)
                    $surveys[] = $courseSurvey;
            }
        }
        // Si le candidat n'appartient pas au centre ou que le questionnaire n'appartient pas à une formation du centre, générer une erreur.
        if (!in_array($candidate, $candidates) | !in_array($survey, $surveys))
            throw throw new AccessDeniedHttpException();
        // Sinon créer l'entité résultat.
        $result = new Result();
        $result->setCandidate($candidate);
        $result->setSurvey($survey);
        $result->setTestDate(new DateTime());

        // Créer un token de validation
        $token = uniqid('', true) . rtrim(strtr(base64_encode(random_bytes(12)), '+/', '-_'), '=');

        $result->setToken($token);
        // Envoyer le mail avec le lien au candidat.

        $email = (new TemplatedEmail())
            ->from(new Address($_ENV['ADMIN_EMAIL'], 'epset mailer'))
            ->to($candidate->getEmail())
            ->subject('QCM formation ' . $survey->getCourse()->getTitle())
            ->htmlTemplate('mailer/email_candidate.html.twig')
            ->context([
                'token' => $token,
                'candidate' => $candidate
            ]);

              $mailer->send($email);

        $resultRepository->add($result, true);

        return $this->render('main/index.html.twig');
    }


    /**
     * @throws \Exception
     */
    #[Route('/{id}', name: 'app_result_show', methods: ['GET'])]
    public function show(Result $result, QuestionRepository $questionRepository): Response
    {
        // Si l'utilisateur n'est pas administrateur gérer l'accès
        if (!$this->isGranted("ROLE_ADMIN")) {
            // Si la fiche de résultats passée en GET est supprimée ou n'appartient pas à un candidat lié au centre, générer une erreur
            if ($result->getDeleteDate() !== null | $result->getCandidate()->getUser() !== $this->getUser())
                throw throw new AccessDeniedHttpException();
        }

        // calcul de la durée théorique du test :
        $questionNb = count($questionRepository->findBy([
            'Survey' => $result->getSurvey(),
            'deleteDate' => null,
        ]));
        $testTime = $questionNb * $result->getSurvey()->getQuestionTimer() * 1.2;
        $testTime = date('H:i:s', $testTime);



        return $this->render('result/show.html.twig', [
            'result' => $result,
            'testTime' => $testTime,
        ]);
    }


    // Pas d'édition de la fiche de résultat possible.
//    #[Route('/{id}/edit', name: 'app_result_edit', methods: ['GET', 'POST'])]
//    public function edit(Request $request, Result $result, ResultRepository $resultRepository): Response
//    {
//        // Si l'utilisateur n'est pas administrateur gérer l'accès
//        if (!$this->isGranted("ROLE_ADMIN")) {
//            // Si la fiche de résultats passée en GET est supprimée ou n'appartient pas à un candidat lié au centre, générer une erreur
//            if ($result->getDeleteDate() !== null | $result->getCandidate()->getUser() !== $this->getUser())
//                throw throw new AccessDeniedHttpException();
//        }
//
//        $form = $this->createForm(ResultType::class, $result);
//        $form->handleRequest($request);
//
//        if ($form->isSubmitted() && $form->isValid()) {
//            $resultRepository->add($result, true);
//
//            return $this->redirectToRoute('app_result_index', [], Response::HTTP_SEE_OTHER);
//        }
//
//        return $this->renderForm('result/edit.html.twig', [
//            'result' => $result,
//            'form' => $form,
//        ]);
//    }


    /**
     * Soft delete (seul l'administrateur peux supprimer définitivement l'élément
     *
     * @param Request $request
     * @param Result $result
     * @param ResultRepository $resultRepository
     * @return Response
     */
    #[Route('/{id}', name: 'app_result_delete', methods: ['POST'])]
    public function softDelete(Request $request, Result $result, ResultRepository $resultRepository): Response
    {
        if ($this->isCsrfTokenValid('delete' . $result->getId(), $request->request->get('_token'))) {
            $resultRepository->softDelete($result, true);
        }
        return $this->redirectToRoute('app_result_index', [], Response::HTTP_SEE_OTHER);
    }


    /**
     * Seuls les administrateurs peuvent annuler la suppression d'un résultat.
     * @IsGranted("ROLE_ADMIN")
     *
     */
    #[Route('/reset/{id}', name: 'app_result_reset', methods: ['POST'])]
    public function reset(Request $request, Result $result, ResultRepository $resultRepository): Response
    {
        if ($this->isCsrfTokenValid('_tokenReset' . $result->getId(), $request->request->get('_tokenReset'))) {
            $resultRepository->cancelRemove($result, true);
            $this->addFlash('alert', 'Résultat restauré !');

        }
        return $this->redirectToRoute('app_survey_index', [], Response::HTTP_SEE_OTHER);
    }

    /**
     * Seul l'administrateur peut supprimer définitivement un résultat.
     * @IsGranted("ROLE_ADMIN")
     * @param Request $request
     * @param Result $result
     * @param ResultRepository $resultRepository
     * @return Response
     */
    #[Route('/hard/{id}', name: 'app_result_hard_delete', methods: ['POST'])]
    public function hardDelete(Request $request, Result $result, ResultRepository $resultRepository): Response
    {
        if ($this->isCsrfTokenValid('hardDelete' . $result->getId(), $request->request->get('_token'))) {
            $resultRepository->hardDelete($result, true);
        }

        return $this->redirectToRoute('app_survey_index', [], Response::HTTP_SEE_OTHER);
    }
}
