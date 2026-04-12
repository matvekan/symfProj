<?php

declare(strict_types=1);

namespace App\Service\User;

use App\DTO\Request\Me\UpdateMeRequestDto;
use App\Entity\User;
use App\Service\UserService;

final class UpdateMeHandler
{
    public function __construct(private UserService $userService)
    {
    }

    public function handle(User $user, UpdateMeRequestDto $requestDto): User
    {
        $updateUserInputDto = $requestDto->toUpdateUserInputDto();

        return $this->userService->updateUser($user, $updateUserInputDto);
    }
}
