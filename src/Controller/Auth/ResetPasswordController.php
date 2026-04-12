<?php

declare(strict_types=1);

namespace App\Controller\Auth;

use App\DTO\Request\Auth\ResetPasswordRequestDto;
use App\DTOValidator\RequestDtoValidator;
use App\Service\Auth\ResetPasswordHandler;
use App\Service\Http\JsonRequestParser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class ResetPasswordController extends AbstractController
{
    public function __construct(
        private ResetPasswordHandler $resetPasswordHandler,
        private JsonRequestParser $jsonRequestParser,
        private RequestDtoValidator $requestDtoValidator,
    )
    {
    }

    #[Route('/api/auth/reset-password', name: 'auth_reset_password', methods: ['POST'])]
    public function __invoke(Request $request): JsonResponse
    {
        try {
            $data = $this->jsonRequestParser->parse($request);
        } catch (\InvalidArgumentException $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }

        $requestDto = ResetPasswordRequestDto::fromArray($data);
        $this->requestDtoValidator->validate($requestDto);

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
