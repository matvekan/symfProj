<?php

declare(strict_types=1);

namespace App\Service\Auth;

use App\DTO\Request\Auth\ResetPasswordRequestDto;
use App\Repository\Contract\PasswordResetRepositoryInterface;
use App\Repository\Contract\UserRepositoryInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class ResetPasswordHandler
{
    public function __construct(
        private PasswordResetRepositoryInterface $passwordResetRepository,
        private UserRepositoryInterface $userRepository,
        private UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    public function handle(ResetPasswordRequestDto $requestDto): void
    {
        $passwordReset = $this->passwordResetRepository->findValidByToken($requestDto->token);
        if ($passwordReset === null) {
            throw new \InvalidArgumentException('Token is invalid or expired.');
        }

        $user = $this->userRepository->findOneByEmail((string) $passwordReset->getEmail());
        if ($user === null) {
            throw new \RuntimeException('User not found.');
        }

        $user->setPassword($this->passwordHasher->hashPassword($user, $requestDto->newPassword));
        $passwordReset->setUsedAt(new \DateTimeImmutable());

        $this->userRepository->store($user);
        $this->passwordResetRepository->store($passwordReset);
    }
}
