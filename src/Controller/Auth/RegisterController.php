<?php

declare(strict_types=1);

namespace App\Controller\Auth;

use App\DTO\Output\User\UserOutputDTO;
use App\DTO\Request\Auth\RegisterUserRequestDto;
use App\Service\Auth\RegisterUserHandler;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
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
    #[OA\Tag(name: 'Auth')]
    #[OA\RequestBody(required: true, content: new OA\JsonContent(ref: new Model(type: RegisterUserRequestDto::class)))]
    #[OA\Response(response: 201, description: 'User created', content: new OA\JsonContent(ref: new Model(type: UserOutputDTO::class)))]
    #[OA\Response(response: 422, description: 'Validation error')]
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
