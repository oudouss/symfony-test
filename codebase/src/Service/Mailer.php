<?php

declare(strict_types=1);

namespace App\Service;

use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Message;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class Mailer
{
    /**
     * @var MailerInterface
     */
    private MailerInterface $mailer;
    private Environment $environment;
    private string $destination;

    public function __construct(MailerInterface $mailer, Environment $environment, string $destination)
    {
        $this->mailer = $mailer;
        $this->environment = $environment;
        $this->destination = $destination;
    }

    /**
     * Sends an email to the admin via the comment form.
     *
     * @param string $from the email address of the user
     * @param string $text the message text sent by the user
     *
     * @throws TransportExceptionInterface
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function notifyAdmin(string $from, string $text, string $articleTitle): void
    {
        $body = $this->environment->render('email/comment.html.twig', ['email' => $from, 'message' => $text, 'article' => $articleTitle]);
        $email = (new Email())
            ->from($from)
            ->to($this->destination)
            ->subject('A new comment have been added!')
            ->html($body);

        $this->mailer->send($email);
    }

}
