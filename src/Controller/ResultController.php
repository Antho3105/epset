<?php

namespace App\Controller;

use App\Entity\Result;
use App\Form\ResultType;
use App\Repository\CandidateRepository;
use App\Repository\ResultRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
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
        if (!$this->isGranted("ROLE_CENTER")) {
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

    #[Route('/new', name: 'app_result_new', methods: ['GET', 'POST'])]
    public function new(Request $request, ResultRepository $resultRepository): Response
    {
        $result = new Result();
        $form = $this->createForm(ResultType::class, $result);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $resultRepository->add($result, true);

            return $this->redirectToRoute('app_result_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('result/new.html.twig', [
            'result' => $result,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_result_show', methods: ['GET'])]
    public function show(Result $result): Response
    {
        // Si l'utilisateur n'est pas administrateur gérer l'accès
        if (!$this->isGranted("ROLE_ADMIN")) {
            // Si la fiche de résultats passée en GET est supprimée ou n'appartient pas à un candidat lié au centre, générer une erreur
            if ($result->getDeleteDate() !== null | $result->getCandidate()->getUser() !== $this->getUser())
                throw throw new AccessDeniedHttpException();
        }
        return $this->render('result/show.html.twig', [
            'result' => $result,
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
