<?php

declare(strict_types=1);

namespace App\Message;

final class PasswordResetEmailMessage
{
    public function __construct(
        public readonly string $email,
        public readonly string $token,
        public readonly string $baseUrl,
        public readonly string $mailFrom,
    ) {
    }
}
