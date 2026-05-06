<?php

declare(strict_types=1);

namespace App\Controller\Admin\User;

use App\DTO\Output\User\UserOutputDTO;
use App\DTO\Request\Admin\User\UpdateAdminUserRequestDto;
use App\Entity\User;
use App\Service\User\UpdateUserHandler;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

final class UpdateAdminUserController extends AbstractController
{
    public function __construct(
        private UpdateUserHandler $updateUserHandler,
        private SerializerInterface $serializer,
    ) {
    }

    #[Route('/api/admin/users/{user}', name: 'admin_users_update', methods: ['PATCH'])]
    #[OA\Tag(name: 'Admin')]
    #[OA\Parameter(name: 'user', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))]
    #[OA\RequestBody(required: true, content: new OA\JsonContent(ref: new Model(type: UpdateAdminUserRequestDto::class)))]
    #[OA\Response(response: 200, description: 'User updated', content: new OA\JsonContent(ref: new Model(type: UserOutputDTO::class)))]
    #[OA\Response(response: 403, description: 'Forbidden')]
    #[OA\Response(response: 422, description: 'Validation error')]
    public function __invoke(User $user, #[MapRequestPayload] UpdateAdminUserRequestDto $requestDto): JsonResponse
    {
        $updated = $this->updateUserHandler->handle($user, $requestDto);

        return new JsonResponse($this->serializer->serialize($updated, 'json', ['groups' => ['user:item']]), 200, [], true);
    }
}
