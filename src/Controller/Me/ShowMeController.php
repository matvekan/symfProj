<?php

declare(strict_types=1);

namespace App\Controller\Me;

use App\ResponseBuilder\UserResponseBuilder;
use App\Service\Security\CurrentUserProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class ShowMeController extends AbstractController
{
    public function __construct(
        private CurrentUserProvider $currentUserProvider,
        private UserResponseBuilder $userResponseBuilder,
    ) {
    }

    #[Route('/api/me', name: 'me_show', methods: ['GET'])]
    public function __invoke(): JsonResponse
    {
        $user = $this->currentUserProvider->getCurrentUser();
        if ($user === null) {
            return $this->json(['error' => 'Unauthorized.'], 401);
        }

        return $this->userResponseBuilder->showUserResponse($user);
    }
}
