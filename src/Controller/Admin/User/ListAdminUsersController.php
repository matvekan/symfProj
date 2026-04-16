<?php

declare(strict_types=1);

namespace App\Controller\Admin\User;

use App\DTO\Request\Admin\User\AdminUserFiltersRequestDto;
use App\Service\Admin\User\ListAdminUsersFetcher;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;

final class ListAdminUsersController extends AbstractController
{
    public function __construct(private ListAdminUsersFetcher $listAdminUsersFetcher)
    {
    }

    #[Route('/api/admin/users', name: 'admin_users_index', methods: ['GET'])]
    public function __invoke(#[MapQueryString] AdminUserFiltersRequestDto $requestDto): JsonResponse
    {
        $payload = $this->listAdminUsersFetcher->fetch($requestDto->toQueryDto());

        return new JsonResponse($payload, 200, [], true);
    }
}
