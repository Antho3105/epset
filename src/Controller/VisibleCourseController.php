<?php

namespace App\Controller;

use App\Entity\VisibleCourse;
use App\Form\VisibleCourseType;
use App\Repository\CourseRepository;
use App\Repository\UserRepository;
use App\Repository\VisibleCourseRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @IsGranted("ROLE_CENTER")
 *
 */
#[Route('/assign/course')]
class VisibleCourseController extends AbstractController
{
//    #[Route('/', name: 'app_visible_course_index', methods: ['GET'])]
//    public function index(VisibleCourseRepository $visibleCourseRepository): Response
//    {
//        return $this->render('visible_course/index.html.twig', [
//            'visible_courses' => $visibleCourseRepository->findAll(),
//        ]);
//    }

    #[Route('/new', name: 'app_visible_course_new', methods: ['GET', 'POST'])]
    public function new(Request $request, VisibleCourseRepository $visibleCourseRepository, UserRepository $userRepository, CourseRepository $courseRepository): Response
    {
        $visibleCourse = new VisibleCourse();
        $form = $this->createForm(VisibleCourseType::class, $visibleCourse);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $visibleCourseRepository->add($visibleCourse, true);
            return $this->redirectToRoute('app_course_index', [], Response::HTTP_SEE_OTHER);
        }
        return $this->renderForm('visible_course/new_old_file.html.twig', [
             'form' => $form,
        ]);
    }

//    #[Route('/{id}', name: 'app_visible_course_show', methods: ['GET'])]
//    public function show(VisibleCourse $visibleCourse): Response
//    {
//        return $this->render('visible_course/show.html.twig', [
//            'visible_course' => $visibleCourse,
//        ]);
//    }
//


//    #[Route('/{id}/edit', name: 'app_visible_course_edit', methods: ['GET', 'POST'])]
//    public function edit(Request $request, VisibleCourse $visibleCourse, VisibleCourseRepository $visibleCourseRepository): Response
//    {
//        $form = $this->createForm(VisibleCourseType::class, $visibleCourse);
//        $form->handleRequest($request);
//
//        if ($form->isSubmitted() && $form->isValid()) {
//            $visibleCourseRepository->add($visibleCourse, true);
//
//            return $this->redirectToRoute('app_course_index', [], Response::HTTP_SEE_OTHER);
//        }
//
//        return $this->renderForm('visible_course/edit.html.twig', [
//            'visible_course' => $visibleCourse,
//            'form' => $form,
//        ]);
//    }

    #[Route('/{id}', name: 'app_visible_course_delete', methods: ['POST'])]
    public function delete(Request $request, VisibleCourse $visibleCourse, VisibleCourseRepository $visibleCourseRepository): Response
    {
        if ($this->isCsrfTokenValid('delete' . $visibleCourse->getId(), $request->request->get('_token'))) {
            $visibleCourseRepository->remove($visibleCourse, true);
        }
        return $this->redirectToRoute('app_course_index', [], Response::HTTP_SEE_OTHER);
    }
}
