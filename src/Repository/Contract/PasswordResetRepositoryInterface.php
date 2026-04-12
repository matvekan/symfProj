<?php

declare(strict_types=1);

namespace App\Repository\Contract;

use App\Entity\PasswordReset;

interface PasswordResetRepositoryInterface
{
    public function store(PasswordReset $passwordReset, bool $isFlush = true): PasswordReset;

    public function findValidByToken(string $token): ?PasswordReset;
}
