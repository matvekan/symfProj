<?php

declare(strict_types=1);

namespace App\Controller\Auth;

use App\DTO\Request\Auth\RegisterUserRequestDto;
use App\Service\Auth\RegisterUserHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

final class RegisterController extends AbstractController
{
    public function __construct(
        private RegisterUserHandler $registerUserHandler,
        private SerializerInterface $serializer,
    ) {
    }

    #[Route('/api/auth/register', name: 'auth_register', methods: ['POST'])]
    public function __invoke(#[MapRequestPayload] RegisterUserRequestDto $requestDto): JsonResponse
    {
        try {
            $user = $this->registerUserHandler->handle($requestDto);

            return new JsonResponse($this->serializer->serialize($user, 'json', ['groups' => ['user:item']]), 201, [], true);
        } catch (\Throwable $e) {
            return $this->json(['error' => $e->getMessage()], 422);
        }
    }
}
