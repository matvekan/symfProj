<?php

declare(strict_types=1);

namespace App\Controller\Admin\User;

use App\DTO\Output\User\UserOutputDTO;
use App\DTO\Request\Admin\User\AdminUserFiltersRequestDto;
use App\Service\Admin\User\ListAdminUsersFetcher;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
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
    #[OA\Tag(name: 'Admin')]
    #[OA\Parameter(name: 'name', in: 'query', schema: new OA\Schema(type: 'string'))]
    #[OA\Parameter(name: 'email', in: 'query', schema: new OA\Schema(type: 'string'))]
    #[OA\Parameter(name: 'role', in: 'query', schema: new OA\Schema(type: 'string'))]
    #[OA\Parameter(name: 'createdFrom', in: 'query', schema: new OA\Schema(type: 'string', format: 'date-time'))]
    #[OA\Parameter(name: 'createdTo', in: 'query', schema: new OA\Schema(type: 'string', format: 'date-time'))]
    #[OA\Parameter(name: 'interestName', in: 'query', schema: new OA\Schema(type: 'string'))]
    #[OA\Parameter(name: 'interestIds[]', in: 'query', schema: new OA\Schema(type: 'array', items: new OA\Items(type: 'integer')))]
    #[OA\Parameter(name: 'page', in: 'query', schema: new OA\Schema(type: 'integer', default: 1))]
    #[OA\Parameter(name: 'limit', in: 'query', schema: new OA\Schema(type: 'integer', default: 20))]
    #[OA\Response(response: 200, description: 'Users list', content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: new Model(type: UserOutputDTO::class))))]
    #[OA\Response(response: 403, description: 'Forbidden')]
    public function __invoke(#[MapQueryString] AdminUserFiltersRequestDto $requestDto): JsonResponse
    {
        $payload = $this->listAdminUsersFetcher->fetch($requestDto->toQueryDto());

        return new JsonResponse($payload, 200, [], true);
    }
}
