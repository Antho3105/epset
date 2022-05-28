<?php

namespace App\Controller;

use App\Entity\Candidate;
use App\Form\CandidateType;
use App\Repository\CandidateRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * @IsGranted("ROLE_CENTER")
 */
#[Route('/candidate')]
class CandidateController extends AbstractController
{
    #[Route('/', name: 'app_candidate_index', methods: ['GET'])]
    public function index(CandidateRepository $candidateRepository): Response
    {
        $user = $this->getUser();
        // Si administrateur afficher tous les candidats
        if ($this->isGranted("ROLE_ADMIN")) {
            return $this->render('candidate/index.html.twig', [
                'candidates' => $candidateRepository->findAll(),
            ]);
        }
        // Sinon n'afficher que les candidats rattachés au centre et non supprimés.
        return $this->render('candidate/index.html.twig', [
            'candidates' => $candidateRepository->findBy(
                ['user' => $user,
                    'deleteDate' => null]
            ),
        ]);
    }

    #[Route('/new', name: 'app_candidate_new', methods: ['GET', 'POST'])]
    public function new(Request $request, CandidateRepository $candidateRepository): Response
    {
        $candidate = new Candidate();
        $form = $this->createForm(CandidateType::class, $candidate);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->getUser();
            // Affecter le nouveau candidat à l'utilisateur actuel.
            // TODO ajouter une fonction admin pour affecter un candidat a un centre.
            $candidate->setUser($user);
            $candidateRepository->add($candidate, true);
            $this->addFlash('success', 'Candidat' . $candidate->getFirstName() . ' ' . $candidate->getLastName() . ' ajouté !');
            return $this->redirectToRoute('app_candidate_index', [], Response::HTTP_SEE_OTHER);
        }
        return $this->renderForm('candidate/new.html.twig', [
            'candidate' => $candidate,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_candidate_show', methods: ['GET'])]
    public function show(Candidate $candidate): Response
    {
        // Si l'utilisateur n'est pas administrateur, gérer l'accès.
        if (!$this->isGranted("ROLE_ADMIN")) {
            // Si la fiche du candidat n'appartient pas au centre ou que la fiche a été supprimée générer une erreur 403.
            if ($this->getUser() !== $candidate->getUser() | $candidate->getDeleteDate() !== null)
            throw new AccessDeniedHttpException();
        }
        return $this->render('candidate/show.html.twig', [
            'candidate' => $candidate,
        ]);
    }

    #[
        Route('/{id}/edit', name: 'app_candidate_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Candidate $candidate, CandidateRepository $candidateRepository): Response
    {
        // Si l'utilisateur n'est pas administrateur, gérer l'accès.
        if (!$this->isGranted("ROLE_ADMIN")) {
            // Si la fiche du candidat n'appartient pas au centre ou que la fiche a été supprimée générer une erreur 403.
            if ($candidate->getUser() !== $this->getUser()  | $candidate->getDeleteDate() !== null)
                throw new AccessDeniedHttpException();
        }
        $form = $this->createForm(CandidateType::class, $candidate);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $candidateRepository->add($candidate, true);
            $this->addFlash('success', 'Fiche de '  . $candidate->getFirstName() . ' ' . $candidate->getLastName() . ' modifiée !');

            return $this->redirectToRoute('app_candidate_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('candidate/edit.html.twig', [
            'candidate' => $candidate,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_candidate_delete', methods: ['POST'])]
    public function delete(Request $request, Candidate $candidate, CandidateRepository $candidateRepository): Response
    {
        // Si l'utilisateur n'est pas administrateur, gérer l'accès.
        if (!$this->isGranted("ROLE_ADMIN")) {
            // Si la fiche du candidat n'appartient pas au centre  ou que la fiche a déjà été supprimée générer une erreur 403.
            if ($this->getUser() !== $candidate->getUser()  | $candidate->getDeleteDate() !== null)
                throw new AccessDeniedHttpException();
        }
        if ($this->isCsrfTokenValid('delete' . $candidate->getId(), $request->request->get('_token'))) {
            $candidateRepository->softDelete($candidate, true);
        }
        $this->addFlash('alert', 'Fiche de '  . $candidate->getFirstName() . ' ' . $candidate->getLastName() . ' supprimée !');

        return $this->redirectToRoute('app_candidate_index', [], Response::HTTP_SEE_OTHER);
    }
}
