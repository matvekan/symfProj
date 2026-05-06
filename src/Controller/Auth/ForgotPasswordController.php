<?php

declare(strict_types=1);

namespace App\Controller\Auth;

use App\DTO\Request\Auth\ForgotPasswordRequestDto;
use App\Service\Auth\ForgotPasswordHandler;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

final class ForgotPasswordController extends AbstractController
{
    public function __construct(
        private ForgotPasswordHandler $forgotPasswordHandler,
    ) {
    }

    #[Route('/api/auth/forgot-password', name: 'auth_forgot_password', methods: ['POST'])]
    #[OA\Tag(name: 'Auth')]
    #[OA\RequestBody(required: true, content: new OA\JsonContent(ref: new Model(type: ForgotPasswordRequestDto::class)))]
    #[OA\Response(response: 200, description: 'Request accepted')]
    public function __invoke(#[MapRequestPayload] ForgotPasswordRequestDto $requestDto): JsonResponse
    {
        $this->forgotPasswordHandler->handle($requestDto);

        return $this->json(['message' => 'If account exists, reset instructions were sent.']);
    }
}
