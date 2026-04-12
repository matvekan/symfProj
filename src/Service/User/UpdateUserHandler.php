<?php

declare(strict_types=1);

namespace App\Service\User;

use App\DTO\Request\Admin\User\UpdateAdminUserRequestDto;
use App\Entity\User;
use App\Service\UserService;

final class UpdateUserHandler
{
    public function __construct(private UserService $userService)
    {
    }

    public function handle(User $user, UpdateAdminUserRequestDto $requestDto): User
    {
        $updateUserInputDto = $requestDto->toUpdateUserInputDto();

        return $this->userService->updateUser($user, $updateUserInputDto);
    }
}
