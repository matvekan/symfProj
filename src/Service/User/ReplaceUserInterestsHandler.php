<?php

declare(strict_types=1);

namespace App\Service\User;

use App\DTO\Input\Common\InterestIdsInputDto;
use App\DTO\Request\Me\ReplaceMeInterestsRequestDto;
use App\Entity\User;
use App\Service\UserService;

final class ReplaceUserInterestsHandler
{
    public function __construct(private UserService $userService)
    {
    }

    public function handle(User $user, ReplaceMeInterestsRequestDto $requestDto): User
    {
        return $this->userService->replaceInterests($user, new InterestIdsInputDto($requestDto->interestIds ?? []));
    }
}
