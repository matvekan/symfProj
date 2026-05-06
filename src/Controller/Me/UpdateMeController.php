<?php

declare(strict_types=1);

namespace App\Controller\Me;

use App\DTO\Output\User\UserOutputDTO;
use App\DTO\Request\Me\UpdateMeRequestDto;
use App\Factory\UserFactory;
use App\Service\Security\CurrentUserProvider;
use App\Service\User\UpdateMeHandler;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

final class UpdateMeController extends AbstractController
{
    public function __construct(
        private CurrentUserProvider $currentUserProvider,
        private UpdateMeHandler $updateMeHandler,
        private UserFactory $userFactory,
    ) {
    }

    #[Route('/api/me', name: 'me_update', methods: ['PATCH'])]
    #[OA\Tag(name: 'Me')]
    #[OA\RequestBody(required: true, content: new OA\JsonContent(ref: new Model(type: UpdateMeRequestDto::class)))]
    #[OA\Response(response: 200, description: 'Updated user', content: new OA\JsonContent(ref: new Model(type: UserOutputDTO::class)))]
    #[OA\Response(response: 401, description: 'Unauthorized')]
    public function __invoke(#[MapRequestPayload] UpdateMeRequestDto $requestDto): JsonResponse
    {
        $user = $this->currentUserProvider->getCurrentUser();
        if (null === $user) {
            return $this->json(['error' => 'Unauthorized.'], 401);
        }

        $updated = $this->updateMeHandler->handle($user, $requestDto);

        return $this->json($this->userFactory->makeStoreUserOutputDTO($updated));
    }
}
