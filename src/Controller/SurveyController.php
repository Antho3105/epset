<?php

namespace App\Controller;

use App\Entity\Survey;
use App\Form\SurveyType;
use App\Repository\SurveyRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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
    public function index(SurveyRepository $surveyRepository): Response
    {
        $user = $this->getUser();
        // Si administrateur afficher toutes les formations
        if ($this->isGranted("ROLE_ADMIN")) {
            return $this->render('survey/index.html.twig', [
                'surveys' => $surveyRepository->findAll(),
            ]);
        }
        // Si formateur n'afficher que les formations qu'il a créé.
        if ($this->isGranted("ROLE_TRAINER")) {
            return $this->render('survey/index.html.twig', [
                'surveys' => $surveyRepository->findBy([
                    'user' => $user
                ])
            ]);
        }

        // Si centre n'afficher que les formations liées à ses formations.
        if ($this->isGranted("ROLE_CENTER")) {
            // TODO gérer l'affichage des questionnaires.
            $surveys = [];

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
     * @param SurveyRepository $surveyRepository
     * @return Response
     */
    #[Route('/new', name: 'app_survey_new', methods: ['GET', 'POST'])]
    public function new(Request $request, SurveyRepository $surveyRepository): Response
    {
        $user = $this->getUser();
        $survey = new Survey();
        $form = $this->createForm(SurveyType::class, $survey);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $survey->setUser($user);
            $surveyRepository->add($survey, true);

            return $this->redirectToRoute('app_survey_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('survey/new.html.twig', [
            'survey' => $survey,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_survey_show', methods: ['GET'])]
    public function show(Survey $survey): Response
    {
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
    #[Route('/{id}/edit', name: 'app_survey_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Survey $survey, SurveyRepository $surveyRepository): Response
    {
        $form = $this->createForm(SurveyType::class, $survey);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $surveyRepository->add($survey, true);

            return $this->redirectToRoute('app_survey_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('survey/edit.html.twig', [
            'survey' => $survey,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_survey_delete', methods: ['POST'])]
    public function delete(Request $request, Survey $survey, SurveyRepository $surveyRepository): Response
    {
        if ($this->isCsrfTokenValid('delete' . $survey->getId(), $request->request->get('_token'))) {
            $surveyRepository->remove($survey, true);
        }

        return $this->redirectToRoute('app_survey_index', [], Response::HTTP_SEE_OTHER);
    }
}
