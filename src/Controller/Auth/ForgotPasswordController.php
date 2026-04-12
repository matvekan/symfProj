<?php

declare(strict_types=1);

namespace App\Controller\Auth;

use App\DTO\Request\Auth\ForgotPasswordRequestDto;
use App\DTOValidator\RequestDtoValidator;
use App\Service\Auth\ForgotPasswordHandler;
use App\Service\Http\JsonRequestParser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Routing\Attribute\Route;

final class ForgotPasswordController extends AbstractController
{
    public function __construct(
        private ForgotPasswordHandler $forgotPasswordHandler,
        private JsonRequestParser $jsonRequestParser,
        private RequestDtoValidator $requestDtoValidator,
        #[Autowire('%kernel.debug%')] private bool $isDebug,
    ) {
    }

    #[Route('/api/auth/forgot-password', name: 'auth_forgot_password', methods: ['POST'])]
    public function __invoke(Request $request): JsonResponse
    {
        try {
            $data = $this->jsonRequestParser->parse($request);
        } catch (\InvalidArgumentException $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }

        $requestDto = ForgotPasswordRequestDto::fromArray($data);
        $this->requestDtoValidator->validate($requestDto);

        try {
            $this->forgotPasswordHandler->handle($requestDto);
        } catch (TransportExceptionInterface $e) {
            $message = 'Email delivery failed. Check SMTP settings.';
            if ($this->isDebug) {
                $message .= ' ' . $e->getMessage();
            }

            return $this->json(['error' => $message], 502);
        }

        return $this->json(['message' => 'If account exists, reset instructions were sent.']);
    }
}
