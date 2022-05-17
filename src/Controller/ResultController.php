<?php

namespace App\Controller;

use App\Entity\Result;
use App\Form\ResultType;
use App\Repository\ResultRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/result')]
class ResultController extends AbstractController
{
    #[Route('/', name: 'app_result_index', methods: ['GET'])]
    public function index(ResultRepository $resultRepository): Response
    {
        return $this->render('result/index.html.twig', [
            'results' => $resultRepository->findAll(),
        ]);
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
        return $this->render('result/show.html.twig', [
            'result' => $result,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_result_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Result $result, ResultRepository $resultRepository): Response
    {
        $form = $this->createForm(ResultType::class, $result);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $resultRepository->add($result, true);

            return $this->redirectToRoute('app_result_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('result/edit.html.twig', [
            'result' => $result,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_result_delete', methods: ['POST'])]
    public function delete(Request $request, Result $result, ResultRepository $resultRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$result->getId(), $request->request->get('_token'))) {
            $resultRepository->remove($result, true);
        }

        return $this->redirectToRoute('app_result_index', [], Response::HTTP_SEE_OTHER);
    }
}
