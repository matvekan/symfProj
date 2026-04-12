<?php

declare(strict_types=1);

namespace App\Service\Auth;

use App\DTO\Request\Auth\ForgotPasswordRequestDto;
use App\Entity\PasswordReset;
use App\Repository\Contract\PasswordResetRepositoryInterface;
use App\Repository\Contract\UserRepositoryInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

final class ForgotPasswordHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private PasswordResetRepositoryInterface $passwordResetRepository,
        private MailerInterface $mailer,
        private LoggerInterface $logger,
        #[Autowire('%env(APP_BASE_URL)%')]
        private string $appBaseUrl,
        #[Autowire('%env(MAIL_FROM)%')]
        private string $mailFrom,
    ) {
    }

    public function handle(ForgotPasswordRequestDto $requestDto): void
    {
        $email = (string) $requestDto->email;
        $user = $this->userRepository->findOneByEmail($email);
        if ($user === null) {
            return;
        }

        $token = bin2hex(random_bytes(32));
        $baseUrl = rtrim($this->appBaseUrl, '/');

        $passwordReset = new PasswordReset();
        $passwordReset->setEmail($email);
        $passwordReset->setToken($token);
        $passwordReset->setCreatedAt(new \DateTimeImmutable());
        $passwordReset->setExpiresAt((new \DateTimeImmutable())->modify('+1 hour'));
        $this->passwordResetRepository->store($passwordReset);

        $message = (new Email())
            ->from($this->mailFrom)
            ->to($email)
            ->subject('Password reset')
            ->text("Use this token to reset password: {$token}\nOr open: {$baseUrl}/forgot-password?token={$token}")
            ->html(sprintf(
                '<p>Your password reset token: <b>%s</b></p><p><a href="%s/forgot-password?token=%s">Open reset page</a></p>',
                htmlspecialchars($token, ENT_QUOTES),
                htmlspecialchars($baseUrl, ENT_QUOTES),
                htmlspecialchars($token, ENT_QUOTES),
            ));

        try {
            $this->mailer->send($message);
        } catch (TransportExceptionInterface $e) {
            $this->logger->error('Password reset email send failed.', [
                'email' => $email,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
