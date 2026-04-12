<?php

declare(strict_types=1);

namespace App\Controller\Me;

use App\DTO\Request\Me\UpdateMeRequestDto;
use App\DTOValidator\RequestDtoValidator;
use App\ResponseBuilder\UserResponseBuilder;
use App\Service\Http\JsonRequestParser;
use App\Service\Security\CurrentUserProvider;
use App\Service\User\UpdateMeHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class UpdateMeController extends AbstractController
{
    public function __construct(
        private CurrentUserProvider $currentUserProvider,
        private UpdateMeHandler $updateMeHandler,
        private UserResponseBuilder $userResponseBuilder,
        private JsonRequestParser $jsonRequestParser,
        private RequestDtoValidator $requestDtoValidator,
    ) {
    }

    #[Route('/api/me', name: 'me_update', methods: ['PATCH'])]
    public function __invoke(Request $request): JsonResponse
    {
        $user = $this->currentUserProvider->getCurrentUser();
        if ($user === null) {
            return $this->json(['error' => 'Unauthorized.'], 401);
        }

        try {
            $data = $this->jsonRequestParser->parse($request);
        } catch (\InvalidArgumentException $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }

        $requestDto = UpdateMeRequestDto::fromArray($data);
        $this->requestDtoValidator->validate($requestDto);

        $updated = $this->updateMeHandler->handle($user, $requestDto);

        return $this->userResponseBuilder->showUserResponse($updated);
    }
}
