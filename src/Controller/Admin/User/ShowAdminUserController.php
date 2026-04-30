<?php

declare(strict_types=1);

namespace App\Controller\Admin\User;

use App\Entity\User;
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
    public function __invoke(User $user): JsonResponse
    {
        return new JsonResponse($this->serializer->serialize($user, 'json', ['groups' => ['user:item']]), 200, [], true);
    }
}
