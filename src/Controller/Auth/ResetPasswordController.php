<?php

declare(strict_types=1);

namespace App\Controller\Auth;

use App\DTO\Request\Auth\ResetPasswordRequestDto;
use App\Service\Auth\ResetPasswordHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

final class ResetPasswordController extends AbstractController
{
    public function __construct(
        private ResetPasswordHandler $resetPasswordHandler,
    ) {
    }

    #[Route('/api/auth/reset-password', name: 'auth_reset_password', methods: ['POST'])]
    public function __invoke(#[MapRequestPayload] ResetPasswordRequestDto $requestDto): JsonResponse
    {
        try {
            $this->resetPasswordHandler->handle($requestDto);
        } catch (\InvalidArgumentException $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        } catch (\RuntimeException $e) {
            return $this->json(['error' => $e->getMessage()], 404);
        }

        return $this->json(['message' => 'Password updated successfully.']);
    }
}
