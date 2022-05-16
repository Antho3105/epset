<?php

namespace App\Controller;

use App\Entity\VisibleCourse;
use App\Form\VisibleCourseAdminType;
use App\Form\VisibleCourseType;
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
    public function new(Request $request, VisibleCourseRepository $visibleCourseRepository): Response
    {
        $user = $this->getUser();
        $visibleCourse = new VisibleCourse();
        // Si administrateur ouvrir le formulaire avec toutes les formations.
        if ($this->isGranted("ROLE_ADMIN")) {
            $form = $this->createForm(VisibleCourseAdminType::class, $visibleCourse);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $visibleCourseRepository->add($visibleCourse, true);
                return $this->redirectToRoute('app_course_index', [], Response::HTTP_SEE_OTHER);
            }
            return $this->renderForm('visible_course/new.html.twig', [
                'form' => $form,
            ]);
        }
        // Si centre n'afficher dans le formulaire que les formations que ce centre propose.
        $form = $this->createForm(VisibleCourseType::class, $visibleCourse);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $visibleCourseRepository->add($visibleCourse, true);
            return $this->redirectToRoute('app_course_index', [], Response::HTTP_SEE_OTHER);
        }
        return $this->renderForm('visible_course/new.html.twig', [
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
