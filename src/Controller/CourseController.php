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
        // TODO debugger la vue...
        if ($this->isGranted("ROLE_TRAINER")) {
            return $this->render('course/index.html.twig', [
                'courses' => $visibleCourseRepository->findBy(
                    ['user' => $user,
                        'deleteDate' => null]
                ),
            ]);
        }

        // Si centre n'afficher que les formations qui lui sont rattachées
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

    #[
        Route('/new', name: 'app_course_new', methods: ['GET', 'POST'])]
    public function new(Request $request, CourseRepository $courseRepository): Response
    {

        $course = new Course();
        $form = $this->createForm(CourseType::class, $course);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $courseRepository->add($course, true);

            return $this->redirectToRoute('app_course_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('course/new.html.twig', [
            'course' => $course,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_course_show', methods: ['GET'])]
    public function show(Course $course): Response
    {
        return $this->render('course/show.html.twig', [
            'course' => $course,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_course_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Course $course, CourseRepository $courseRepository): Response
    {
        $form = $this->createForm(CourseType::class, $course);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $courseRepository->add($course, true);

            return $this->redirectToRoute('app_course_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('course/edit.html.twig', [
            'course' => $course,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_course_delete', methods: ['POST'])]
    public function delete(Request $request, Course $course, CourseRepository $courseRepository): Response
    {
        if ($this->isCsrfTokenValid('delete' . $course->getId(), $request->request->get('_token'))) {
            $courseRepository->remove($course, true);
        }

        return $this->redirectToRoute('app_course_index', [], Response::HTTP_SEE_OTHER);
    }
}
