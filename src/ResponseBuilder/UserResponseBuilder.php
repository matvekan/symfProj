<?php

declare(strict_types=1);

namespace App\ResponseBuilder;

use App\Entity\User;
use App\Factory\UserFactory;
use App\Resource\UserResourse;
use Symfony\Component\HttpFoundation\JsonResponse;

final class UserResponseBuilder
{
    public function __construct(private UserResourse $userResourse, private UserFactory $userFactory)
    {
    }

    public function storeUserResponse(User $user, int $status = 200, array $headers = [], bool $isJson = true): JsonResponse
    {
        $userOutputDTO = $this->userFactory->makeStoreUserOutputDTO($user);

        $userResourse = $this->userResourse->userItem($userOutputDTO);

        return new JsonResponse($userResourse, $status, $headers, $isJson);
    }

    public function showUserResponse(User $user, int $status = 200, array $headers = [], bool $isJson = true): JsonResponse
    {
        $userOutputDTO = $this->userFactory->makeStoreUserOutputDTO($user);
        $userResourse = $this->userResourse->userItem($userOutputDTO);

        return new JsonResponse($userResourse, $status, $headers, $isJson);
    }
}
