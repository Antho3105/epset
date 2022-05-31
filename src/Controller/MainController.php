<?php

namespace App\Controller;

use App\Form\ContactType;
use App\Repository\UserRepository;
use DateTime;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Mailer\MailerInterface;

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

    /**
     *
     *
     * @param Request $request
     * @return Response
     */
    #[Route('/contact', name: 'app_contact')]
    public function contact(Request $request, MailerInterface $mailer): Response
    {
        $form = $this->createForm(ContactType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $contactFormData = $form->getData();
            $message = (new Email())
                ->from($contactFormData['email'])
                ->to(new Address($_ENV['ADMIN_EMAIL']))
                ->subject('vous avez reçu unn email')
                ->text('Sender : ' . $contactFormData['email'] . \PHP_EOL .
                    $contactFormData['message'],
                    'text/plain');
            $mailer->send($message);

            $this->addFlash('success', 'Votre message a été envoyé !');

            return $this->redirectToRoute('app_home');
        }

        return $this->render('contact/contact.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
