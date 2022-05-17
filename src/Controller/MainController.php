<?php

namespace App\Controller;

use App\Repository\UserRepository;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(UserRepository $userRepository): Response
    {
        // si l'utilisateur n'est pas connecté rediriger vers la page de login.
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }
        // si connecté mettre a jour la date de dernière connexion.
        $date = new DateTime();
        $user = $this->getUser()->setLastConnection($date);
        $userRepository->add($user, true);

        return $this->render('main/index.html.twig');
    }
}
