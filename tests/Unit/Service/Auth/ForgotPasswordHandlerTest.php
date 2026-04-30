<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Auth;

use App\DTO\Request\Auth\ForgotPasswordRequestDto;
use App\Entity\PasswordReset;
use App\Entity\User;
use App\Message\PasswordResetEmailMessage;
use App\Repository\Contract\PasswordResetRepositoryInterface;
use App\Repository\Contract\UserRepositoryInterface;
use App\Service\Auth\ForgotPasswordHandler;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

#[AllowMockObjectsWithoutExpectations]
final class ForgotPasswordHandlerTest extends TestCase
{
    public function testHandleDoesNothingWhenUserNotFound(): void
    {
        $userRepository = $this->createMock(UserRepositoryInterface::class);
        $passwordResetRepository = $this->createMock(PasswordResetRepositoryInterface::class);
        $messageBus = $this->createMock(MessageBusInterface::class);

        $userRepository->method('findOneByEmail')->willReturn(null);
        $passwordResetRepository->expects(self::never())->method('store');
        $messageBus->expects(self::never())->method('dispatch');

        $handler = new ForgotPasswordHandler(
            $userRepository,
            $passwordResetRepository,
            $messageBus,
            'http://127.0.0.1/',
            'no-reply@symfproj.local',
        );

        $handler->handle(new ForgotPasswordRequestDto('missing@example.com'));
    }

    public function testHandleStoresTokenAndDispatchesMessage(): void
    {
        $user = new User();
        $user->setEmail('john@example.com');

        $userRepository = $this->createMock(UserRepositoryInterface::class);
        $passwordResetRepository = $this->createMock(PasswordResetRepositoryInterface::class);
        $messageBus = $this->createMock(MessageBusInterface::class);

        $userRepository->method('findOneByEmail')->willReturn($user);

        $passwordResetRepository
            ->expects(self::once())
            ->method('store')
            ->with(self::callback(static function (PasswordReset $reset): bool {
                return 'john@example.com' === $reset->getEmail()
                    && \is_string($reset->getToken())
                    && 64 === \strlen((string) $reset->getToken())
                    && $reset->getCreatedAt() instanceof \DateTimeImmutable
                    && $reset->getExpiresAt() instanceof \DateTimeImmutable;
            }));

        $messageBus
            ->expects(self::once())
            ->method('dispatch')
            ->with(self::callback(static function (PasswordResetEmailMessage $message): bool {
                return 'john@example.com' === $message->email
                    && 64 === \strlen($message->token)
                    && 'http://127.0.0.1' === $message->baseUrl
                    && 'no-reply@symfproj.local' === $message->mailFrom;
            }))
            ->willReturn(new Envelope(new \stdClass()));

        $handler = new ForgotPasswordHandler(
            $userRepository,
            $passwordResetRepository,
            $messageBus,
            'http://127.0.0.1/',
            'no-reply@symfproj.local',
        );

        $handler->handle(new ForgotPasswordRequestDto('john@example.com'));
    }
}
