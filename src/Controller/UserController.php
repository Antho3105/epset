<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

class UserController extends AbstractController
{
    /**
     *
     * @IsGranted("ROLE_ADMIN")
     * @param UserRepository $userRepository
     * @return Response
     */
    #[Route('/user/list', name: 'app_user_list')]
    public function indexUser(UserRepository $userRepository): Response
    {
        return $this->render('user/index.html.twig', [
            'users' => $userRepository->findAll()
        ]);
    }

    /**
     *
     * @IsGranted("ROLE_ADMIN")
     * @param UserRepository $userRepository
     * @return Response
     */
    #[Route('/center/list', name: 'app_center_list')]
    public function indexCenter(UserRepository $userRepository): Response
    {
        return $this->render('user/index.html.twig', [
            // TODO lister les formateurs uniquement.
            'users' => $userRepository->findByRole("ROLE_CENTER")
        ]);
    }

    /**
     *
     * @IsGranted("ROLE_CENTER")
     * @param UserRepository $userRepository
     * @return Response
     */
    #[Route('/trainer/list', name: 'app_trainer_list')]
    public function indexTrainer(UserRepository $userRepository): Response
    {
        return $this->render('user/index.html.twig', [
            'users' => $userRepository->findByRole("ROLE_TRAINER")
        ]);
    }
}
