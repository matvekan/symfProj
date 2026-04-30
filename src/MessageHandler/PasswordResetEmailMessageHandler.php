<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Message\PasswordResetEmailMessage;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Mime\Email;

#[AsMessageHandler]
final class PasswordResetEmailMessageHandler
{
    public function __construct(
        private MailerInterface $mailer,
        private LoggerInterface $logger,
    ) {
    }

    public function __invoke(PasswordResetEmailMessage $message): void
    {
        $email = (new Email())
            ->from($message->mailFrom)
            ->to($message->email)
            ->subject('Password reset')
            ->text("Use this token to reset password: {$message->token}\nOr open: {$message->baseUrl}/forgot-password?token={$message->token}")
            ->html(\sprintf(
                '<p>Your password reset token: <b>%s</b></p><p><a href="%s/forgot-password?token=%s">Open reset page</a></p>',
                htmlspecialchars($message->token, \ENT_QUOTES),
                htmlspecialchars($message->baseUrl, \ENT_QUOTES),
                htmlspecialchars($message->token, \ENT_QUOTES),
            ));

        try {
            $this->mailer->send($email);
        } catch (TransportExceptionInterface $e) {
            $this->logger->error('Password reset email send failed.', [
                'email' => $message->email,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
