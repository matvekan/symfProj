<?php

declare(strict_types=1);

namespace App\Controller\Me;

use App\DTO\Output\User\UserOutputDTO;
use App\Factory\UserFactory;
use App\Service\Security\CurrentUserProvider;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class ShowMeController extends AbstractController
{
    public function __construct(
        private CurrentUserProvider $currentUserProvider,
        private UserFactory $userFactory,
    ) {
    }

    #[Route('/api/me', name: 'me_show', methods: ['GET'])]
    #[OA\Tag(name: 'Me')]
    #[OA\Response(response: 200, description: 'Current user', content: new OA\JsonContent(ref: new Model(type: UserOutputDTO::class)))]
    #[OA\Response(response: 401, description: 'Unauthorized')]
    public function __invoke(): JsonResponse
    {
        $user = $this->currentUserProvider->getCurrentUser();
        if (null === $user) {
            return $this->json(['error' => 'Unauthorized.'], 401);
        }

        return $this->json($this->userFactory->makeStoreUserOutputDTO($user));
    }
}
