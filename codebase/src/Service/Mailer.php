<?php

declare(strict_types=1);

namespace App\Service;

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\SyntaxError;
use Twig\Error\RuntimeError;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

class Mailer
{
    /**
     * @var MailerInterface
     */
    private MailerInterface $mailer;
    private Environment $environment;
    private string $destination;

    public function __construct(
        Environment $environment,
        MailerInterface $mailer,
        string $destination
    )
    {
        $this->mailer = $mailer;
        $this->environment = $environment;
        $this->destination = $destination;
    }

    /**
     * Sends an email to the admin when a comment is added.
     *
     * @param string $from the email address of the user
     * @param string $text the message text sent by the user
     * @param string $articleTitle the article commented by the user
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
            ->subject('Comment added to article: '.$articleTitle)
            ->html($body);

        $this->mailer->send($email);
    }

}
