<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\User;

use App\DTO\Input\Common\InterestIdsInputDto;
use App\DTO\Request\Me\ReplaceMeInterestsRequestDto;
use App\Entity\User;
use App\Service\User\ReplaceUserInterestsHandler;
use App\Service\UserService;
use PHPUnit\Framework\TestCase;

final class ReplaceUserInterestsHandlerTest extends TestCase
{
    public function testHandleDelegatesToUserServiceWithWrappedInterestIds(): void
    {
        $user = new User();
        $requestDto = new ReplaceMeInterestsRequestDto([1, 3, 5]);

        $userService = $this->createMock(UserService::class);
        $userService
            ->expects(self::once())
            ->method('replaceInterests')
            ->with($user, self::callback(static fn (InterestIdsInputDto $dto): bool => $dto->normalized() === [1, 3, 5]))
            ->willReturn($user);

        $handler = new ReplaceUserInterestsHandler($userService);

        self::assertSame($user, $handler->handle($user, $requestDto));
    }
}
