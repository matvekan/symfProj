<?php

declare(strict_types=1);

namespace App\Controller\Admin\User;

use App\DTO\Request\Admin\User\UpdateAdminUserRequestDto;
use App\DTOValidator\RequestDtoValidator;
use App\Entity\User;
use App\ResponseBuilder\UserResponseBuilder;
use App\Service\Http\JsonRequestParser;
use App\Service\User\UpdateUserHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class UpdateAdminUserController extends AbstractController
{
    public function __construct(
        private UpdateUserHandler $updateUserHandler,
        private UserResponseBuilder $userResponseBuilder,
        private JsonRequestParser $jsonRequestParser,
        private RequestDtoValidator $requestDtoValidator,
    ) {
    }

    #[Route('/api/admin/users/{user}', name: 'admin_users_update', methods: ['PATCH'])]
    public function __invoke(User $user, Request $request): JsonResponse
    {
        try {
            $data = $this->jsonRequestParser->parse($request);
        } catch (\InvalidArgumentException $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }

        $requestDto = UpdateAdminUserRequestDto::fromArray($data);
        $this->requestDtoValidator->validate($requestDto);

        $updated = $this->updateUserHandler->handle($user, $requestDto);

        return $this->userResponseBuilder->showUserResponse($updated);
    }
}
