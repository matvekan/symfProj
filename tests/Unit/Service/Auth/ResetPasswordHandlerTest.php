<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Auth;

use App\DTO\Request\Auth\ResetPasswordRequestDto;
use App\Entity\PasswordReset;
use App\Entity\User;
use App\Repository\Contract\PasswordResetRepositoryInterface;
use App\Repository\Contract\UserRepositoryInterface;
use App\Service\Auth\ResetPasswordHandler;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AllowMockObjectsWithoutExpectations]
final class ResetPasswordHandlerTest extends TestCase
{
    public function testHandleThrowsWhenTokenInvalid(): void
    {
        $passwordResetRepository = $this->createMock(PasswordResetRepositoryInterface::class);
        $userRepository = $this->createMock(UserRepositoryInterface::class);
        $hasher = $this->createMock(UserPasswordHasherInterface::class);

        $passwordResetRepository->method('findValidByToken')->willReturn(null);

        $handler = new ResetPasswordHandler($passwordResetRepository, $userRepository, $hasher);

        $this->expectException(\InvalidArgumentException::class);
        $handler->handle(new ResetPasswordRequestDto('bad-token', 'new-password'));
    }

    public function testHandleThrowsWhenUserNotFound(): void
    {
        $reset = (new PasswordReset())
            ->setToken('token')
            ->setEmail('john@example.com')
            ->setCreatedAt(new \DateTimeImmutable())
            ->setExpiresAt((new \DateTimeImmutable())->modify('+1 hour'));

        $passwordResetRepository = $this->createMock(PasswordResetRepositoryInterface::class);
        $userRepository = $this->createMock(UserRepositoryInterface::class);
        $hasher = $this->createMock(UserPasswordHasherInterface::class);

        $passwordResetRepository->method('findValidByToken')->willReturn($reset);
        $userRepository->method('findOneByEmail')->willReturn(null);

        $handler = new ResetPasswordHandler($passwordResetRepository, $userRepository, $hasher);

        $this->expectException(\RuntimeException::class);
        $handler->handle(new ResetPasswordRequestDto('token', 'new-password'));
    }

    public function testHandleHashesPasswordAndMarksTokenAsUsed(): void
    {
        $reset = (new PasswordReset())
            ->setToken('token')
            ->setEmail('john@example.com')
            ->setCreatedAt(new \DateTimeImmutable())
            ->setExpiresAt((new \DateTimeImmutable())->modify('+1 hour'));

        $user = new User();
        $user->setEmail('john@example.com');

        $passwordResetRepository = $this->createMock(PasswordResetRepositoryInterface::class);
        $userRepository = $this->createMock(UserRepositoryInterface::class);
        $hasher = $this->createMock(UserPasswordHasherInterface::class);

        $passwordResetRepository->method('findValidByToken')->willReturn($reset);
        $userRepository->method('findOneByEmail')->willReturn($user);
        $hasher->method('hashPassword')->willReturn('hashed-password');

        $userRepository->expects(self::once())->method('store')->with($user);
        $passwordResetRepository->expects(self::once())->method('store')->with($reset);

        $handler = new ResetPasswordHandler($passwordResetRepository, $userRepository, $hasher);
        $handler->handle(new ResetPasswordRequestDto('token', 'new-password'));

        self::assertSame('hashed-password', $user->getPassword());
        self::assertInstanceOf(\DateTimeImmutable::class, $reset->getUsedAt());
    }
}
