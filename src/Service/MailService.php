<?php

namespace App\Service;

use Symfony\Bridge\Twig\Mime\BodyRenderer;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mailer\Transport;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class MailService {
    private MailerInterface $mailer;

    public function __construct(MailerInterface $mailer){
        //$this->mailer = $mailer;

        $transport = Transport::fromDsn($_ENV['MAILER_DSN']);
        $this->mailer = new Mailer($transport);
    }

    public function sendEmail(
        string $from,
        string $subject,
        string $htmlTemplate,
        array $context,
        string $to='admin@symrecipe.com'
    ):void{
        $email = (new TemplatedEmail())
            ->from($from)
            ->to($to)
            ->subject($subject)
            // path of the Twig template to render
            ->htmlTemplate($htmlTemplate)

            // pass variables (name => value) to the template
            ->context($context);
        //dd($email, $this->mailer);

        //$loader = new FilesystemLoader('../templates/');
        $loader = new FilesystemLoader('templates/');
                $twigEnv = new Environment($loader);
                $twigBodyRenderer = new BodyRenderer($twigEnv);
                $twigBodyRenderer->render($email);

        $this->mailer->send($email);
    }
}