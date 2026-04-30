<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\User;

use App\DTO\Input\User\UpdateUserInputDto;
use App\DTO\Request\Admin\User\UpdateAdminUserRequestDto;
use App\Entity\User;
use App\Service\User\UpdateUserHandler;
use App\Service\UserService;
use PHPUnit\Framework\TestCase;

final class UpdateUserHandlerTest extends TestCase
{
    public function testHandleDelegatesToUserService(): void
    {
        $user = new User();
        $requestDto = new UpdateAdminUserRequestDto(role: 'ROLE_ADMIN');

        $userService = $this->createMock(UserService::class);
        $userService
            ->expects(self::once())
            ->method('updateUser')
            ->with($user, self::callback(static fn (UpdateUserInputDto $dto): bool => 'ROLE_ADMIN' === $dto->role))
            ->willReturn($user);

        $handler = new UpdateUserHandler($userService);

        self::assertSame($user, $handler->handle($user, $requestDto));
    }
}
