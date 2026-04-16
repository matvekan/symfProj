<?php

declare(strict_types=1);

namespace App\DTO\Request\Auth;

use Symfony\Component\Validator\Constraints as Assert;

final class ForgotPasswordRequestDto
{
    public function __construct(
        #[Assert\NotBlank(message: 'Email is required.')]
        #[Assert\Email(message: 'email is not valid.')]
        public readonly string $email = '',
    ) {
    }
}
