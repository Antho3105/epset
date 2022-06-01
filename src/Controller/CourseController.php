<?php

namespace App\Controller;

use App\Entity\Course;
use App\Form\CourseType;
use App\Repository\CourseRepository;
use App\Repository\VisibleCourseRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;


/**
 * @IsGranted("ROLE_USER")
 */
#[Route('/course')]
class CourseController extends AbstractController
{
    #[Route('/', name: 'app_course_index', methods: ['GET'])]
    public function index(CourseRepository $courseRepository, VisibleCourseRepository $visibleCourseRepository): Response
    {
        $user = $this->getUser();
        // Si administrateur afficher toutes les formations
        if ($this->isGranted("ROLE_ADMIN")) {
            return $this->render('course/index.html.twig', [
                'courses' => $courseRepository->findAll(),
            ]);
        }
        // Si formateur n'afficher que les formations qui lui sont rattachées
        // (si les formations sont supprimée le lien avec le formateur l'est aussi automatiquement)
        if ($this->isGranted("ROLE_TRAINER")) {
            $courses = $visibleCourseRepository->findBy(
                ['user' => $user,
                    ]);
            $visibleCourses = [];
            foreach ($courses as $course) {
                $visibleCourses[] = $courseRepository->findOneBy(['id' => $course->getCourse()]);
            }
            return $this->render('course/index.html.twig', [
                'courses' => $visibleCourses
            ]);
        }

        // Si centre n'afficher que les formations qui lui sont rattachées et non supprimées.
        if ($this->isGranted("ROLE_CENTER")) {
            return $this->render('course/index.html.twig', [
                'courses' => $courseRepository->findBy(
                    ['user' => $user,
                        'deleteDate' => null]
                ),
            ]);
        }
        return $this->render('main/index.html.twig');
    }

    /**
     * Seuls les centre ont les droits pour créer une nouvelle formation.
     * @IsGranted("ROLE_CENTER")
     *
     */
    #[Route('/new', name: 'app_course_new', methods: ['GET', 'POST'])]
    public function new(Request $request, CourseRepository $courseRepository): Response
    {
        $course = new Course();
        $form = $this->createForm(CourseType::class, $course);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$course->getDetail()){
                $this->addFlash('alert', 'Il manque le détail !');
                return $this->renderForm('course/new.html.twig', [
                    'course' => $course,
                    'form' => $form,
                ]);
            }
            $user = $this->getUser();
            $course->setUser($user);
            $courseRepository->add($course, true);
            $this->addFlash('success', 'formation ajoutée !');
            return $this->redirectToRoute('app_course_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('course/new.html.twig', [
            'course' => $course,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_course_show', methods: ['GET'])]
    public function show(Course $course, VisibleCourseRepository $visibleCourseRepository): Response
    {
        $user = $this->getUser();
        // Si l'utilisateur n'est pas administrateur, gérer l'accès.
        if (!$this->isGranted("ROLE_ADMIN")) {
            // Si la formation pas n'appartient pas au centre ou qu'elle a été supprimée générer une erreur.
            if ($this->isGranted("ROLE_CENTER")) {
                if ($user !== $course->getUser() | $course->getDeleteDate() !== null)
                    throw new AccessDeniedHttpException();
            }

            // Si la formation n'est pas affectée au formateur générer une erreur.
            // Si la formation est supprimée le lien avec le formateur est supprimé automatiquement.
            if ($this->isGranted("ROLE_TRAINER")) {
                $isVisible = $visibleCourseRepository->findBy([
                    'user' => $user,
                    'Course' => $course
                ]);
                if (!$isVisible)
                    throw new AccessDeniedHttpException();
            }
        }
        return $this->render('course/show.html.twig', [
            'course' => $course,
        ]);
    }

    /**
     * Seuls les centres ont les droits pour modifier une formation.
     * @IsGranted("ROLE_CENTER")
     *
     */
    #[Route('/{id}/edit', name: 'app_course_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Course $course, CourseRepository $courseRepository): Response
    {
        $user = $this->getUser();
        if (!$this->isGranted("ROLE_ADMIN")) {
            // Si la formation n'appartient pas au centre ou qu'elle a été supprimée générer une erreur.
            if ($user !== $course->getUser() | $course->getDeleteDate() !== null)
                throw new AccessDeniedHttpException();
        }
        $form = $this->createForm(CourseType::class, $course);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $courseRepository->add($course, true);
            $this->addFlash('success', 'Formation modifiée !');
            return $this->redirectToRoute('app_course_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('course/edit.html.twig', [
            'course' => $course,
            'form' => $form,
        ]);
    }

    /**
     * Seuls les centre ont les droits pour supprimer une formation.
     * Soft delete (suppression definitive accessible par l'administrateur uniquement)
     * @IsGranted("ROLE_CENTER")
     *
     */
    #[Route('/{id}', name: 'app_course_delete', methods: ['POST'])]
    public function delete(Request $request, Course $course, CourseRepository $courseRepository, VisibleCourseRepository $visibleCourseRepository): Response
    {
        if ($this->isCsrfTokenValid('delete' . $course->getId(), $request->request->get('_token'))) {
            $assignments = $visibleCourseRepository->findBy(['Course' => $course]);
            // suppression de toutes les assignations de formateurs pour la formation à supprimer.
            foreach ($assignments as $assignment) {
                $visibleCourseRepository->remove($assignment);
            }
            // supprimer la formation (écrire la date de suppression dans la propriété deleteDate)
            $courseRepository->softRemove($course, true);
            $this->addFlash('alert', 'Formation supprimée !');
        }

        return $this->redirectToRoute('app_course_index', [], Response::HTTP_SEE_OTHER);
    }

    /**
     * Seuls les administrateurs peuvent annuler la suppression d'une formation.
     * @IsGranted("ROLE_ADMIN")
     *
     */
    #[Route('/reset/{id}', name: 'app_course_reset', methods: ['POST'])]
    public function reset(Request $request, Course $course, CourseRepository $courseRepository): Response
    {
        if ($this->isCsrfTokenValid('_tokenReset' . $course->getId(), $request->request->get('_tokenReset'))) {
            // appeler la fonction qui permet de remettre à null la propriété delete date.
            $courseRepository->cancelRemove($course, true);
            $this->addFlash('alert', 'Formation restaurée !');
        }
        return $this->redirectToRoute('app_course_index', [], Response::HTTP_SEE_OTHER);
    }
}
