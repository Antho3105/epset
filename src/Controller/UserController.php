<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\CenterType;
use App\Form\UserType;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
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
            'users' => $userRepository->findAll(),
            'type' => "utilisateurs"
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
            'users' => $userRepository->findByRole("ROLE_CENTER"),
            'type' => "centres"
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
            'users' => $userRepository->findByRole("ROLE_TRAINER"),
            'type' => "formateurs"
        ]);
    }

    /**
     *
     * @IsGranted("ROLE_CENTER")
     * @param Request $request
     * @param UserRepository $userRepository
     * @return Response
     */
    #[Route('/search/result', name: 'app_search_user')]
    public function search(Request $request, UserRepository $userRepository): Response
    {
        $lastName = $request->get('lastName');
        $firstName = $request->get('firstName');

        if ($lastName && $firstName) {
            $users = $userRepository->createQueryBuilder('user')
                ->where('user.roles LIKE :roles')
                ->andWhere('user.lastName LIKE :lastname')
                ->andWhere('user.firstName LIKE :firstname')
                ->setParameter('roles', '%"' . "ROLE_TRAINER" . '"%')
                ->setParameter('lastname', $lastName)
                ->setParameter('firstname', $firstName)
                ->getQuery()
                ->getResult();
        } elseif ($lastName && !$firstName) {
            $users = $userRepository->createQueryBuilder('user')
                ->where('user.roles LIKE :roles')
                ->andWhere('user.lastName LIKE :lastname')
                ->setParameter('roles', '%"' . "ROLE_TRAINER" . '"%')
                ->setParameter('lastname', $lastName)
                ->getQuery()
                ->getResult();
        } elseif (!$lastName && $firstName) {
            $users = $userRepository->createQueryBuilder('user')
                ->where('user.roles LIKE :roles')
                ->andWhere('user.firstName LIKE :firstname')
                ->setParameter('roles', '%"' . "ROLE_TRAINER" . '"%')
                ->setParameter('firstname', $firstName)
                ->getQuery()
                ->getResult();
        }


        return $this->render('user/index.html.twig', [
            'users' => $users,
            'type' => "formateurs"
        ]);
    }

    /**
     * Affiche la page de detail d'un utilisateur
     * Un Administrateur peut tout voir
     * Un centre ne peut voir que les formateurs
     * Un formateur ne peut pas accéder au pages de details.
     *
     * @IsGranted("ROLE_USER")
     * @param UserRepository $userRepository
     * @param User $user
     * @return Response
     */
    #[Route('/user/{id}', name: 'app_user_show')]
    public function showUser(UserRepository $userRepository, User $user): Response
    {
        // si l'utilisateur n'est pas admin gerer l'acces
        if (!$this->isGranted("ROLE_ADMIN")) {
            // Si l'utilisateur est un formateur interdire l'accès aux detail des utilisateurs
            if ($this->isGranted("ROLE_TRAINER")) {
                throw throw new AccessDeniedHttpException();
            }
            // Si l'utilisateur en un centre générer une erreur s'il veut voir une autre fiche que celle d'un formateur..
            if ($this->isGranted("ROLE_CENTER")) {
                if ($user->getRoles()[0] !== "ROLE_TRAINER") {
                    throw throw new AccessDeniedHttpException();
                }
            }
        }
        return $this->render('user/show.html.twig', [
            'user' => $user,
        ]);
    }

    /**
     *
     */
    #[Route('/user/edit/{id}', name: 'app_user_edit')]
    public function editUser(Request $request, UserRepository $userRepository, User $user): Response
    {
        if (!$this->isGranted("ROLE_ADMIN")) {
            // Interdire l'accès a une autre page que celle de l'utilisateur en cours
            if ($this->getUser() !== $user) {
                throw throw new AccessDeniedHttpException();
            }
        }

        if ($user->getRoles()[0] == "ROLE_CENTER")
        $form = $this->createForm(CenterType::class, $user);
        else
            $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $userRepository->add($user, true);
            $this->addFlash('success', 'Fiche modifiée !');
            return $this->redirectToRoute('app_home', [], Response::HTTP_SEE_OTHER);
        }


        return $this->renderForm('user/edit.html.twig', [
            'form' => $form,
            'user' => $user
        ]);
    }


}
