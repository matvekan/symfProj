<?php

namespace App\Controller;

use App\DTOValidator\UserDTOValidator;
use App\Entity\PasswordReset;
use App\Factory\UserFactory;
use App\Repository\PasswordResetRepository;
use App\Repository\UserRepository;
use App\ResponseBuilder\UserResponseBuilder;
use App\Service\UserService;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

final class AuthController extends AbstractController
{
    public function __construct(
        private UserFactory $userFactory,
        private UserDTOValidator $userDTOValidator,
        private UserService $userService,
        private UserResponseBuilder $userResponseBuilder,
        private UserRepository $userRepository,
        private PasswordResetRepository $passwordResetRepository,
        private MailerInterface $mailer,
        private UserPasswordHasherInterface $passwordHasher,
        #[Autowire('%env(APP_BASE_URL)%')] private string $appBaseUrl,
        #[Autowire('%env(MAIL_FROM)%')] private string $mailFrom,
    ) {
    }

    #[Route('/api/auth/register', name: 'auth_register', methods: ['POST'])]
    public function register(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if (!is_array($data)) {
            return $this->json(['error' => 'Invalid JSON body.'], 400);
        }

        $data['role'] = 'ROLE_USER';
        $data['created_at'] = $data['created_at'] ?? (new \DateTimeImmutable())->format('Y-m-d H:i:s');

        try {
            $storeUserInputDTO = $this->userFactory->makeStoreUserInputDTO($data);
            $this->userDTOValidator->validate($storeUserInputDTO);
            $user = $this->userService->store($storeUserInputDTO);

            return $this->userResponseBuilder->storeUserResponse($user, 201);
        } catch (\Throwable $e) {
            return $this->json(['error' => $e->getMessage()], 422);
        }
    }

    #[Route('/api/auth/forgot-password', name: 'auth_forgot_password', methods: ['POST'])]
    public function forgotPassword(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $email = is_array($data) ? ($data['email'] ?? '') : '';
        if (!is_string($email) || $email === '') {
            return $this->json(['error' => 'Email is required.'], 400);
        }

        $user = $this->userRepository->findOneByEmail($email);
        if ($user !== null) {
            $token = bin2hex(random_bytes(32));
            $baseUrl = rtrim($this->appBaseUrl, '/');
            $passwordReset = new PasswordReset();
            $passwordReset->setEmail($email);
            $passwordReset->setToken($token);
            $passwordReset->setCreatedAt(new \DateTimeImmutable());
            $passwordReset->setExpiresAt((new \DateTimeImmutable())->modify('+1 hour'));
            $this->passwordResetRepository->store($passwordReset);

            $message = (new Email())
                ->from($this->mailFrom)
                ->to($email)
                ->subject('Password reset')
                ->text("Use this token to reset password: {$token}\nOr open: {$baseUrl}/forgot-password?token={$token}")
                ->html(sprintf(
                    '<p>Your password reset token: <b>%s</b></p><p><a href="%s/forgot-password?token=%s">Open reset page</a></p>',
                    htmlspecialchars($token, ENT_QUOTES),
                    htmlspecialchars($baseUrl, ENT_QUOTES),
                    htmlspecialchars($token, ENT_QUOTES)
                ));
            $this->mailer->send($message);
        }

        return $this->json(['message' => 'If account exists, reset instructions were sent.']);
    }

    #[Route('/api/auth/reset-password', name: 'auth_reset_password', methods: ['POST'])]
    public function resetPassword(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if (!is_array($data)) {
            return $this->json(['error' => 'Invalid JSON body.'], 400);
        }

        $token = $data['token'] ?? null;
        $newPassword = $data['newPassword'] ?? null;

        if (!is_string($token) || $token === '' || !is_string($newPassword) || $newPassword === '') {
            return $this->json(['error' => 'token and newPassword are required.'], 400);
        }

        $passwordReset = $this->passwordResetRepository->findValidByToken($token);
        if ($passwordReset === null) {
            return $this->json(['error' => 'Token is invalid or expired.'], 400);
        }

        $user = $this->userRepository->findOneByEmail((string) $passwordReset->getEmail());
        if ($user === null) {
            return $this->json(['error' => 'User not found.'], 404);
        }

        $user->setPassword($this->passwordHasher->hashPassword($user, $newPassword));
        $passwordReset->setUsedAt(new \DateTimeImmutable());

        $this->userRepository->store($user);
        $this->passwordResetRepository->store($passwordReset);

        return $this->json(['message' => 'Password updated successfully.']);
    }
}
