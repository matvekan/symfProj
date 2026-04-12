<?php

declare(strict_types=1);

namespace App\Controller\Admin\User;

use App\Entity\User;
use App\ResponseBuilder\UserResponseBuilder;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class ShowAdminUserController extends AbstractController
{
    public function __construct(private UserResponseBuilder $userResponseBuilder)
    {
    }

    #[Route('/api/admin/users/{user}', name: 'admin_users_show', methods: ['GET'])]
    public function __invoke(User $user): JsonResponse
    {
        return $this->userResponseBuilder->showUserResponse($user);
    }
}
