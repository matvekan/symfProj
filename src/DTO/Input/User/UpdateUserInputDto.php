<?php

declare(strict_types=1);

namespace App\DTO\Input\User;

final class UpdateUserInputDto
{
    public ?string $name = null;

    public ?string $email = null;

    public ?string $role = null;

    public ?string $password = null;

    public bool $hasInterestIds = false;

    /**
     * @var array<int, int>
     */
    public array $interestIds = [];
}
