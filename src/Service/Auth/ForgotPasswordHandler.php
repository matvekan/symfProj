<?php

declare(strict_types=1);

namespace App\Service\Auth;

use App\DTO\Request\Auth\ForgotPasswordRequestDto;
use App\Entity\PasswordReset;
use App\Message\PasswordResetEmailMessage;
use App\Repository\Contract\PasswordResetRepositoryInterface;
use App\Repository\Contract\UserRepositoryInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Messenger\MessageBusInterface;

final class ForgotPasswordHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private PasswordResetRepositoryInterface $passwordResetRepository,
        private MessageBusInterface $messageBus,
        #[Autowire('%env(APP_BASE_URL)%')]
        private string $appBaseUrl,
        #[Autowire('%env(MAIL_FROM)%')]
        private string $mailFrom,
    ) {
    }

    public function handle(ForgotPasswordRequestDto $requestDto): void
    {
        $user = $this->userRepository->findOneByEmail($requestDto->email);
        if (null === $user) {
            return;
        }

        $token = bin2hex(random_bytes(32));
        $baseUrl = rtrim($this->appBaseUrl, '/');

        $passwordReset = new PasswordReset();
        $passwordReset->setEmail($requestDto->email);
        $passwordReset->setToken($token);
        $passwordReset->setCreatedAt(new \DateTimeImmutable());
        $passwordReset->setExpiresAt((new \DateTimeImmutable())->modify('+1 hour'));
        $this->passwordResetRepository->store($passwordReset);

        $this->messageBus->dispatch(new PasswordResetEmailMessage(
            email: $requestDto->email,
            token: $token,
            baseUrl: $baseUrl,
            mailFrom: $this->mailFrom,
        ));
    }
}
