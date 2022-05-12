<?php

namespace App\Controller;

use phpDocumentor\Reflection\Types\True_;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;

class MailerController extends AbstractController
{
//    #[Route('/mailer', name: 'app_mailer')]
//    public function index(): Response
//    {
//        return $this->render('mailer/index.html.twig', [
//            'controller_name' => 'MailerController',
//        ]);
//    }
    #[Route('/email/send')]
    public function sendEmail(MailerInterface $mailer): Response
    {
        $email = (new Email())
            ->from($_ENV['ADMIN_EMAIL'])
            ->to('a.airaud.dev@gmail.com')
            //->cc('cc@example.com')
            //->bcc('bcc@example.com')
            //->replyTo('fabien@example.com')
            //->priority(Email::PRIORITY_HIGH)
            ->subject('Test Mail Symfony !')
            ->text('You\'ve done it !!')
            ->html('<p>See Twig integration for better HTML integration!</p>');

        $mailer->send($email);

        // ...

        return $this->render('main/index.html.twig', [
        ]);
    }

}
