<?php

namespace App\Controller;

use App\Entity\Course;
use App\Entity\Survey;
use App\Form\SurveyType;
use App\Repository\CourseRepository;
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
        // Si administrateur afficher toutes les formations
        if ($this->isGranted("ROLE_ADMIN")) {
            return $this->render('survey/index.html.twig', [
                'surveys' => $surveyRepository->findAll(),
            ]);
        }
        // Si formateur n'afficher que les formations qu'il a créées et qui ne sont pas supprimées.
        if ($this->isGranted("ROLE_TRAINER")) {
            return $this->render('survey/index.html.twig', [
                'surveys' => $surveyRepository->findBy([
                    'user' => $user,
                    'deleteDate' => null
                ])
            ]);
        }

        // Si centre n'afficher que les formations liées à ses formations.
        if ($this->isGranted("ROLE_CENTER")) {
            // Aller récupérer toutes les formations du centre qui ne sont pas supprimées.
            $courses = $courseRepository->findBy([
                'user' => $user,
                'deleteDate' => null
            ]);
            $surveys = [];
            // Pour chaque formation aller récupérer tous les questionnaires.
            foreach ($courses as $course) {
                $courseSurveys = $surveyRepository->findBy([
                    'course' => $course
                ]);
                // Ajouter chaque questionnaire à la liste.
                foreach ($courseSurveys as $courseSurvey) {
                    $surveys[] = $courseSurvey;
                }
            }
            // Rendre la vue avec la liste des questionnaires.
            return $this->render('survey/index.html.twig', [
                'surveys' => $surveys,
            ]);
        }

        return $this->render('main/index.html.twig', [
        ]);
    }

    /**
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
        // N'autoriser l'accès au formulaire de création d'un questionnaire que si le formateur est assigné.
        $user = $this->getUser();
        // Récupérer la liste des formations assignées.
        $visibleCourses = $visibleCourseRepository->findBy([
            'user' => $user
        ]);
        $allowedCourses = [];
        foreach ($visibleCourses as $visibleCourse)
            $allowedCourses[] = $visibleCourse->getCourse();
        // Verifier que la formation passée en GET est dans le tableau des formations autorisées.
        if (!in_array($course, $allowedCourses))
            throw throw new AccessDeniedHttpException();

        $survey = new Survey();
        $form = $this->createForm(SurveyType::class, $survey);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $survey->setUser($user);
            $survey->setCourse($course);
            $surveyRepository->add($survey, true);
            $this->addFlash('success', 'Questionnaire ajouté !');
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
     *
     * @param Survey $survey
     * @param VisibleCourseRepository $visibleCourseRepository
     * @return Response
     */
    #[Route('/{id}', name: 'app_survey_show', methods: ['GET'])]
    public function show(Survey $survey, VisibleCourseRepository $visibleCourseRepository): Response
    {
        $user = $this->getUser();
        // Si l'utilisateur n'est pas admin gérer les accès.
        if (!$this->isGranted("ROLE_ADMIN")) {
            // Si FORMATEUR n'autoriser l'accès que si le questionnaire est lié a une formation dont il a accès.
            if ($this->isGranted("ROLE_TRAINER")) {
                // Si le questionnaire à été supprimé générer une erreur.
                if ($survey->getDeleteDate() !== null)
                    throw throw new AccessDeniedHttpException();
                // Récupérer la formation sur laquelle est lié le questionnaire
                $course = $survey->getCourse();
                // Récupérer la liste des formations accessibles.
                $visibleCourses = $visibleCourseRepository->findBy([
                    'user' => $user
                ]);
                // initialiser le tableau des formations accessible.
                $courses = [];
                foreach ($visibleCourses as $visibleCourse)
                    $courses[] = $visibleCourse->getCourse();
                // Si la formation recue en GET n'est pas dans le tableau générer ue erreur.
                if (!in_array($course, $courses))
                    throw throw new AccessDeniedHttpException();

            }
            // Si CENTRE n'autoriser l'accès que si le questionnaire appartient à une de ses formations.
            if ($this->isGranted("ROLE_CENTRE")) {

                if ($survey->getCourse()->getUser() !== $user | $survey->getDeleteDate() !== null)
                    throw throw new AccessDeniedHttpException();
            }
        }
        return $this->render('survey/show.html.twig', [
            'survey' => $survey,
        ]);
    }

    /**
     * @IsGranted("ROLE_TRAINER")
     *
     * @param Request $request
     * @param Survey $survey
     * @param SurveyRepository $surveyRepository
     * @return Response
     */
    #[
        Route('/{id}/edit', name: 'app_survey_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Survey $survey, SurveyRepository $surveyRepository): Response
    {
        $form = $this->createForm(SurveyType::class, $survey);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $surveyRepository->add($survey, true);

            $this->addFlash('success', 'Questionnaire modifié !');
            return $this->redirectToRoute('app_survey_show', ['id' => $survey->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('survey/edit.html.twig', [
            'survey' => $survey,
            'form' => $form,
        ]);
    }

    /**
     * @IsGranted("ROLE_ADMIN")
     *
     * @param Request $request
     * @param Survey $survey
     * @param SurveyRepository $surveyRepository
     * @return Response
     */
    #[Route('/hard/{id}', name: 'app_survey_hard_delete', methods: ['POST'])]
    public function delete(Request $request, Survey $survey, SurveyRepository $surveyRepository): Response
    {
        if ($this->isCsrfTokenValid('delete' . $survey->getId(), $request->request->get('_token'))) {
            $surveyRepository->remove($survey, true);
        }

        return $this->redirectToRoute('app_survey_index', [], Response::HTTP_SEE_OTHER);
    }


    /**
     * Seuls les centre ont les droits pour supprimer un questionnaire.
     * Soft delete (suppression definitive accessible par l'administrateur uniquement)
     * @Security("is_granted('ROLE_CENTER') or is_granted('ROLE_TRAINER')")
     *
     */
    #[Route('/{id}', name: 'app_survey_delete', methods: ['POST'])]
    public function softDelete(Request $request, Survey $survey, SurveyRepository $surveyRepository): Response
    {
        if ($this->isCsrfTokenValid('delete' . $survey->getId(), $request->request->get('_token'))) {
            $surveyRepository->softRemove($survey, true);
            $this->addFlash('alert', 'Questionnaire supprimé !');
        }
        return $this->redirectToRoute('app_survey_index', [], Response::HTTP_SEE_OTHER);
    }

    /**
     * Seuls les administrateurs peuvent annuler la suppression d'une formation.
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
}
