<?php

namespace App\Controller;

use App\Form\UploadFile;
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
     * @param SurveyRepository $surveyRepository
     * @param string|null $token
     * @return Response
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('begin/survey/{token]', name: 'app_survey_init', methods: ['GET'])]
    public function begin(Request $request, QuestionRepository $questionRepository, ResultRepository $resultRepository, SurveyRepository $surveyRepository, string $token = null): Response
    {
        // Récupérer le token passé en GET et le stocker dans la session.
        if ($token = $request->query->get('token')) {
            $this->getSessionService()->set('accessToken', $token);
            // Rediriger vers la route sans le token pour ne pas le laisser visible.
            return $this->redirectToRoute('app_survey_init');
        }

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

        // créer le formulaire pour l'upload des fichiers (CV et lettre de motivation.
        $form = $this->createForm(UploadFile::class, );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {


            return $this->redirectToRoute('app_survey_next', [], Response::HTTP_SEE_OTHER);
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
    #[Route('next/question/survey/{token]', name: 'app_survey_next', methods: ['GET'])]
    public function next(Request $request, QuestionRepository $questionRepository, ResultRepository $resultRepository, SurveyRepository $surveyRepository, string $token = null): Response
    {

        return true;
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

    private function getTokenFromSession(): ?string
    {
        return $this->getSessionService()->get('accessToken');
    }

}
