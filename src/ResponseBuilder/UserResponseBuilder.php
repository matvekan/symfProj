<?php

namespace App\ResponseBuilder;

use App\Entity\User;
use App\Factory\UserFactory;
use App\Resource\UserResourse;
use App\DTOValidator\UserDTOValidator;
use Symfony\Component\HttpFoundation\JsonResponse;

class UserResponseBuilder
{
    public function __construct(private UserResourse $userResourse,private UserFactory $userFactory)
    {

    }

    public function storeUserResponse(User $user, $status=200, $headers=[], $isJson=true): JsonResponse{
        $userOutputDTO=$this->userFactory->makeStoreUserOutputDTO($user);

        $userResourse = $this->userResourse->userItem($userOutputDTO);
        return new JsonResponse($userResourse, $status, $headers, $isJson);
    }

    public function indexUserResponse(array $users, $status=200, $headers=[], $isJson=true): JsonResponse{
        $userOutputDTOs=$this->userFactory->makeUserOutputDTOs($users);
        $userResourse = $this->userResourse->userCollection($userOutputDTOs);
        return new JsonResponse($userResourse, $status, $headers, $isJson);
    }

    public function showUserResponse(User $user, $status=200, $headers=[], $isJson=true): JsonResponse{
        $userOutputDTO=$this->userFactory->makeStoreUserOutputDTO($user);
        $userResourse = $this->userResourse->userItem($userOutputDTO);
        return new JsonResponse($userResourse, $status, $headers, $isJson);
    }

}