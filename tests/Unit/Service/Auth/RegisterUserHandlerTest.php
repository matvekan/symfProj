<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Auth;

use App\DTO\Input\User\StoreUserInputDTO;
use App\DTO\Request\Auth\RegisterUserRequestDto;
use App\Entity\User;
use App\Service\Auth\RegisterUserHandler;
use App\Service\UserService;
use PHPUnit\Framework\TestCase;

final class RegisterUserHandlerTest extends TestCase
{
    public function testHandleBuildsInputAndDelegatesToUserService(): void
    {
        $requestDto = new RegisterUserRequestDto('John Doe', 'john@example.com', 'secret', [1, 2]);
        $expectedUser = new User();
        $expectedUser->setEmail('john@example.com');

        $userService = $this->createMock(UserService::class);
        $userService
            ->expects(self::once())
            ->method('store')
            ->with(self::callback(static function (StoreUserInputDTO $dto): bool {
                return 'John Doe' === $dto->name
                    && 'john@example.com' === $dto->email
                    && 'secret' === $dto->password
                    && 'ROLE_USER' === $dto->role
                    && $dto->interestIds === [1, 2]
                    && $dto->createdAt instanceof \DateTimeImmutable;
            }))
            ->willReturn($expectedUser);

        $handler = new RegisterUserHandler($userService);

        self::assertSame($expectedUser, $handler->handle($requestDto));
    }
}
