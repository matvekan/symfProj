<?php

declare(strict_types=1);

namespace App\Service\Auth;

use App\DTO\Input\User\StoreUserInputDTO;
use App\DTO\Request\Auth\RegisterUserRequestDto;
use App\Entity\User;
use App\Service\UserService;

final class RegisterUserHandler
{
    public function __construct(
        private UserService $userService,
    ) {
    }

    public function handle(RegisterUserRequestDto $requestDto): User
    {
        $storeUserInputDto = new StoreUserInputDTO(
            name: $requestDto->name,
            email: $requestDto->email,
            password: $requestDto->password,
            role: 'ROLE_USER',
            createdAt: new \DateTimeImmutable(),
            interestIds: $requestDto->interestIds,
        );

        return $this->userService->store($storeUserInputDto);
    }
}
