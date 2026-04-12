<?php

declare(strict_types=1);

namespace App\Service\Auth;

use App\DTO\Input\User\StoreUserInputDTO;
use App\DTO\Request\Auth\RegisterUserRequestDto;
use App\DTOValidator\UserDTOValidator;
use App\Entity\User;
use App\Factory\UserFactory;
use App\Service\UserService;

final class RegisterUserHandler
{
    public function __construct(
        private UserFactory $userFactory,
        private UserDTOValidator $userDTOValidator,
        private UserService $userService,
    ) {
    }

    public function handle(RegisterUserRequestDto $requestDto): User
    {
        $storeUserInputDto = new StoreUserInputDTO();
        $storeUserInputDto->name = $requestDto->name;
        $storeUserInputDto->email = $requestDto->email;
        $storeUserInputDto->password = $requestDto->password;
        $storeUserInputDto->role = 'ROLE_USER';
        $storeUserInputDto->createdAt = new \DateTimeImmutable();
        $storeUserInputDto->interestIds = $requestDto->interestIds;

        $this->userDTOValidator->validate($storeUserInputDto);

        return $this->userService->store($storeUserInputDto);
    }
}
