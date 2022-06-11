<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Course;
use App\Entity\Survey;
use App\Form\SurveyType;
use App\Repository\CourseRepository;
use App\Repository\QuestionRepository;
use App\Repository\SurveyRepository;
use App\Repository\VisibleCourseRepository;
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
#[Route('/survey')]
class SurveyController extends AbstractController
{
    #[Route('/', name: 'app_survey_index', methods: ['GET'])]
    public function index(SurveyRepository $surveyRepository, CourseRepository $courseRepository): Response
    {
        $user = $this->getUser();
        // Si administrateur afficher tous les questionnaires.
        if ($this->isGranted("ROLE_ADMIN")) {
            return $this->render('survey/index.html.twig', [
                'surveys' => $surveyRepository->findAll(),
            ]);
        }
        // Si formateur n'afficher que les questionnaires qu'il a créés et qui ne sont pas supprimées.
        if ($this->isGranted("ROLE_TRAINER")) {
            return $this->render('survey/index.html.twig', [
                'surveys' => $surveyRepository->findBy([
                    'user' => $user,
                    'deleteDate' => null
                ])
            ]);
        }

        // Si centre n'afficher que les questionnaires liés à ses formations.
        if ($this->isGranted("ROLE_CENTER")) {
            // Aller récupérer toutes les formations du centre qui ne sont pas supprimées.
            $courses = $courseRepository->findBy([
                'user' => $user,
                'deleteDate' => null
            ]);
            $surveys = [];
            // Pour chaque formation aller récupérer tous les questionnaires qui sont lui liés.
            foreach ($courses as $course) {
                $courseSurveys = $surveyRepository->findBy([
                    'course' => $course
                ]);
                // Ajouter chaque questionnaire à la liste sauf s'il a été supprimé.
                foreach ($courseSurveys as $courseSurvey) {
                    if ($courseSurvey->getDeleteDate() === null)
                        $surveys[] = $courseSurvey;
                }
            }
            // Rendre la vue avec la liste des questionnaires.
            return $this->render('survey/index.html.twig', [
                'surveys' => $surveys,
            ]);
        }

        return $this->render('main/index.html.twig');
    }

    /**
     *
     * Seuls les formateurs ont le droit de créer un questionnaire.
     * @IsGranted("ROLE_TRAINER")
     *
     * @param Request $request
     * @param Course $course
     * @param SurveyRepository $surveyRepository
     * @param VisibleCourseRepository $visibleCourseRepository
     * @return Response
     */
    #[Route('/new/{id}', name: 'app_survey_new', methods: ['GET', 'POST'])]
    public function new(Request $request, Course $course, SurveyRepository $surveyRepository, VisibleCourseRepository $visibleCourseRepository): Response
    {
        // Si l'utilisateur n'est pas administrateur gérer l'accès.
        $user = $this->getUser();
        if (!$this->isGranted("ROLE_ADMIN")) {
            // N'autoriser l'accès au formulaire de création d'un questionnaire que si le formateur est assigné.
            // Récupérer la liste des formations assignées.
            $visibleCourses = $visibleCourseRepository->findBy([
                'user' => $user
            ]);
            // Initialiser le tableau des formations assignées
            $allowedCourses = [];
            foreach ($visibleCourses as $visibleCourse)
                $allowedCourses[] = $visibleCourse->getCourse();
            // Si la formation passée en GET n'est pas dans le tableau des formations autorisées générer une erreur.
            if (!in_array($course, $allowedCourses))
                throw new AccessDeniedHttpException();
        }
        // Sinon rendre le formulaire de création.
        $survey = new Survey();
        $form = $this->createForm(SurveyType::class, $survey);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$survey->getDetail()) {
                $this->addFlash('alert', 'Il manque le détail.');
                return $this->renderForm('survey/new.html.twig', [
                    'survey' => $survey,
                    'form' => $form,
                    'course' => $course,
                ]);
            }
            // Attribuer le questionnaire au formateur.
            $survey->setUser($user);
            // Attribuer le questionnaire à la formation.
            $survey->setCourse($course);
            $surveyRepository->add($survey, true);
            $this->addFlash('success', 'Questionnaire ajouté.');
            // Récupérer le dernier questionnaire enregistré par le formateur pour afficher les détails.
            $survey = $surveyRepository->findOneBy([
                'user' => $user], [
                'id' => 'DESC'
            ]);
            return $this->redirectToRoute('app_survey_show', ['id' => $survey->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('survey/new.html.twig', [
            'survey' => $survey,
            'form' => $form,
            'course' => $course,
        ]);
    }

    /**
     * Affiche le questionnaire et les questions qui lui sont liées.
     *
     * @param Survey $survey
     * @param VisibleCourseRepository $visibleCourseRepository
     * @param QuestionRepository $questionRepository
     * @return Response
     */
    #[Route('/{id}', name: 'app_survey_show', methods: ['GET'])]
    public function show(Survey $survey, VisibleCourseRepository $visibleCourseRepository, QuestionRepository $questionRepository): Response
    {
        $user = $this->getUser();
        // Si l'utilisateur n'est pas administrateur gérer les accès.
        if (!$this->isGranted("ROLE_ADMIN")) {
            // Si le questionnaire passé en GET a été supprimé générer une erreur.
            if ($survey->getDeleteDate() !== null)
                throw new AccessDeniedHttpException();
            // Si FORMATEUR n'autoriser l'accès que si le questionnaire est lié à une formation dont il a accès.
            if ($this->isGranted("ROLE_TRAINER")) {
                // Récupérer la formation sur laquelle est lié le questionnaire
                $course = $survey->getCourse();
                // Récupérer la liste des formations accessibles.
                $visibleCourses = $visibleCourseRepository->findBy([
                    'user' => $user
                ]);
                // initialiser le tableau des formations accessibles.
                $courses = [];
                foreach ($visibleCourses as $visibleCourse)
                    $courses[] = $visibleCourse->getCourse();
                // Si la formation recue en GET n'est pas dans le tableau générer ue erreur.
                if (!in_array($course, $courses))
                    throw new AccessDeniedHttpException();

            }
            // Si CENTRE n'autoriser l'accès que si le questionnaire appartient à une de ses formations.
            if ($this->isGranted("ROLE_CENTRE")) {
                if ($survey->getCourse()->getUser() !== $user)
                    throw new AccessDeniedHttpException();
            }
        }

        // Récupérer la liste des questions du questionnaire.
        $questions = $questionRepository->findBy([
            'Survey' => $survey,
            'deleteDate' => null
        ]);

        return $this->render('survey/show.html.twig', [
            'survey' => $survey,
            'questions' => $questions
        ]);
    }

    /**
     * La modification de questionnaire n'est accessible que par les formateurs
     * @IsGranted("ROLE_TRAINER")
     *
     * @param Request $request
     * @param Survey $survey
     * @param SurveyRepository $surveyRepository
     * @return Response
     */
    #[Route('/edit/{id}', name: 'app_survey_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Survey $survey, SurveyRepository $surveyRepository): Response
    {
        // Si l'utilisateur n'est pas administrateur gérer l'accès.
        if (!$this->isGranted("ROLE_ADMIN")) {
            // Si le questionnaire passé en GET n'appartient pas au formateur ou a été supprimé générer une erreur
            if ($survey->getUser() !== $this->getUser() | $survey->getDeleteDate() !== null)
                throw new AccessDeniedHttpException();
        }
        // Sinon créer le formulaire et l'afficher.
        $form = $this->createForm(SurveyType::class, $survey);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // TODO debugger l'édition de questionnaire.
            $surveyRepository->add($survey, true);
            $this->addFlash('success', 'Questionnaire modifié.');
            return $this->redirectToRoute('app_survey_show', ['id' => $survey->getId()], Response::HTTP_SEE_OTHER);
        }
        return $this->renderForm('survey/edit.html.twig', [
            'form' => $form,
        ]);
    }

    /**
     * Les centres et les formateurs peuvent supprimer un questionnaire.
     * Soft delete (suppression definitive accessible par l'administrateur uniquement)
     * @IsGranted("ROLE_TRAINER")
     *
     */
    #[Route('/{id}', name: 'app_survey_delete', methods: ['POST'])]
    public function softDelete(Request $request, Survey $survey, SurveyRepository $surveyRepository): Response
    {
        $user = $this->getUser();
        // Si le questionnaire a déjà été supprimé générer une erreur.
        if ($survey->getDeleteDate() !== null)
            throw new AccessDeniedHttpException();
        // Si l'utilisateur n'est pas administrateur gérer l'accès
        if (!$this->isGranted("ROLE_ADMIN")) {
            // Si le questionnaire n'appartient pas au formateur générer une erreur.
            if ($this->isGranted("ROLE_TRAINER")) {
                if ($survey->getUser() !== $user)
                    throw new AccessDeniedHttpException();
            }
            // Si la formation à laquelle est liée le questionnaire n'appartient pas au centre générer une erreur
            if ($this->isGranted("ROLE_CENTER")) {
                if ($survey->getCourse()->getUser() !== $user)
                    throw new AccessDeniedHttpException();
            }
        }
        // Sinon appeler la fonction de softDelete
        if ($this->isCsrfTokenValid('delete' . $survey->getId(), $request->request->get('_token'))) {
            $surveyRepository->softRemove($survey, true);
            $this->addFlash('alert', 'Questionnaire supprimé !');
        }
        return $this->redirectToRoute('app_survey_index', [], Response::HTTP_SEE_OTHER);
    }

    /**
     * Seuls les administrateurs peuvent restaurer une formation.
     * @IsGranted("ROLE_ADMIN")
     *
     */
    #[Route('/reset/{id}', name: 'app_survey_reset', methods: ['POST'])]
    public function reset(Request $request, Survey $survey, SurveyRepository $surveyRepository): Response
    {
        if ($this->isCsrfTokenValid('_tokenReset' . $survey->getId(), $request->request->get('_tokenReset'))) {
            $surveyRepository->cancelRemove($survey, true);
            $this->addFlash('alert', 'Questionnaire restauré !');

        }
        return $this->redirectToRoute('app_survey_index', [], Response::HTTP_SEE_OTHER);
    }

    /**
     * Seuls l'administrateur peut supprimer définitivement un questionnaire.
     * @IsGranted("ROLE_ADMIN")
     *
     * @param Request $request
     * @param Survey $survey
     * @param SurveyRepository $surveyRepository
     * @return Response
     */
    #[Route('/hardDelete/{id}', name: 'app_survey_hard_delete', methods: ['POST'])]
    public function delete(Request $request, Survey $survey, SurveyRepository $surveyRepository): Response
    {
        if ($this->isCsrfTokenValid('delete' . $survey->getId(), $request->request->get('_token'))) {
            $surveyRepository->remove($survey, true);
        }

        return $this->redirectToRoute('app_survey_index', [], Response::HTTP_SEE_OTHER);
    }

}
