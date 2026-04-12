<?php

declare(strict_types=1);

namespace App\Controller\Auth;

use App\DTO\Request\Auth\RegisterUserRequestDto;
use App\DTOValidator\RequestDtoValidator;
use App\ResponseBuilder\UserResponseBuilder;
use App\Service\Auth\RegisterUserHandler;
use App\Service\Http\JsonRequestParser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class RegisterController extends AbstractController
{
    public function __construct(
        private RegisterUserHandler $registerUserHandler,
        private UserResponseBuilder $userResponseBuilder,
        private JsonRequestParser $jsonRequestParser,
        private RequestDtoValidator $requestDtoValidator,
    ) {
    }

    #[Route('/api/auth/register', name: 'auth_register', methods: ['POST'])]
    public function __invoke(Request $request): JsonResponse
    {
        try {
            $data = $this->jsonRequestParser->parse($request);
        } catch (\InvalidArgumentException $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }

        $requestDto = RegisterUserRequestDto::fromArray($data);
        $this->requestDtoValidator->validate($requestDto);

        try {
            $user = $this->registerUserHandler->handle($requestDto);

            return $this->userResponseBuilder->storeUserResponse($user, 201);
        } catch (\Throwable $e) {
            return $this->json(['error' => $e->getMessage()], 422);
        }
    }
}
