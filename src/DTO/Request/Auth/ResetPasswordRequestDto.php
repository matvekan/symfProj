<?php

declare(strict_types=1);

namespace App\DTO\Request\Auth;

use Symfony\Component\Validator\Constraints as Assert;

final class ResetPasswordRequestDto
{
    public function __construct(
        #[Assert\NotBlank(message: 'token is required.')]
        public readonly string $token = '',
        #[Assert\NotBlank(message: 'newPassword is required.')]
        public readonly string $newPassword = '',
    ) {
    }
}
