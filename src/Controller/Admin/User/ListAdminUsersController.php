<?php

declare(strict_types=1);

namespace App\Controller\Admin\User;

use App\Service\Admin\User\ListAdminUsersHandler;
use App\Service\Admin\User\AdminUserQueryFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class ListAdminUsersController extends AbstractController
{
    public function __construct(
        private ListAdminUsersHandler $listAdminUsersHandler,
        private AdminUserQueryFactory $adminUserQueryFactory,
    )
    {
    }

    #[Route('/api/admin/users', name: 'admin_users_index', methods: ['GET'])]
    public function __invoke(Request $request): JsonResponse
    {
        $queryDto = $this->adminUserQueryFactory->fromRequest($request);
        $payload = $this->listAdminUsersHandler->handle($queryDto);

        return new JsonResponse($payload, 200, [], true);
    }
}
