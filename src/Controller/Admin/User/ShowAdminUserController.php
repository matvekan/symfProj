<?php

declare(strict_types=1);

namespace App\Controller\Admin\User;

use App\DTO\Output\User\UserOutputDTO;
use App\Entity\User;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

final class ShowAdminUserController extends AbstractController
{
    public function __construct(private SerializerInterface $serializer)
    {
    }

    #[Route('/api/admin/users/{user}', name: 'admin_users_show', methods: ['GET'])]
    #[OA\Tag(name: 'Admin')]
    #[OA\Parameter(name: 'user', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))]
    #[OA\Response(response: 200, description: 'User details', content: new OA\JsonContent(ref: new Model(type: UserOutputDTO::class)))]
    #[OA\Response(response: 403, description: 'Forbidden')]
    public function __invoke(User $user): JsonResponse
    {
        return new JsonResponse($this->serializer->serialize($user, 'json', ['groups' => ['user:item']]), 200, [], true);
    }
}
