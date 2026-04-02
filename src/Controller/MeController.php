<?php

namespace App\Controller;

use App\Entity\User;
use App\ResponseBuilder\UserResponseBuilder;
use App\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class MeController extends AbstractController
{
    public function __construct(
        private UserResponseBuilder $userResponseBuilder,
        private UserService $userService,
    ) {
    }

    #[Route('/api/me', name: 'me_show', methods: ['GET'])]
    public function show(): JsonResponse
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            return $this->json(['error' => 'Unauthorized.'], 401);
        }

        return $this->userResponseBuilder->showUserResponse($user);
    }

    #[Route('/api/me', name: 'me_update', methods: ['PATCH'])]
    public function update(Request $request): JsonResponse
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            return $this->json(['error' => 'Unauthorized.'], 401);
        }

        $data = json_decode($request->getContent(), true);
        if (!is_array($data)) {
            return $this->json(['error' => 'Invalid JSON body.'], 400);
        }

        unset($data['role']);

        $updated = $this->userService->updateUser($user, $data);

        return $this->userResponseBuilder->showUserResponse($updated);
    }

    #[Route('/api/me/interests', name: 'me_interests_replace', methods: ['PUT'])]
    public function replaceInterests(Request $request): JsonResponse
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            return $this->json(['error' => 'Unauthorized.'], 401);
        }

        $data = json_decode($request->getContent(), true);
        if (!is_array($data)) {
            return $this->json(['error' => 'Invalid JSON body.'], 400);
        }

        $interestIds = $data['interestIds'] ?? $data['interest_ids'] ?? [];
        if (!is_array($interestIds)) {
            return $this->json(['error' => 'interestIds must be an array.'], 422);
        }

        $updated = $this->userService->replaceInterests($user, $interestIds);

        return $this->userResponseBuilder->showUserResponse($updated);
    }
}
