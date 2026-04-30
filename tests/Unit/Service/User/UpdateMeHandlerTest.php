<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\User;

use App\DTO\Input\User\UpdateUserInputDto;
use App\DTO\Request\Me\UpdateMeRequestDto;
use App\Entity\User;
use App\Service\User\UpdateMeHandler;
use App\Service\UserService;
use PHPUnit\Framework\TestCase;

final class UpdateMeHandlerTest extends TestCase
{
    public function testHandleDelegatesToUserService(): void
    {
        $user = new User();
        $requestDto = new UpdateMeRequestDto(name: 'New Name');

        $userService = $this->createMock(UserService::class);
        $userService
            ->expects(self::once())
            ->method('updateUser')
            ->with($user, self::callback(static fn (UpdateUserInputDto $dto): bool => 'New Name' === $dto->name))
            ->willReturn($user);

        $handler = new UpdateMeHandler($userService);

        self::assertSame($user, $handler->handle($user, $requestDto));
    }
}
