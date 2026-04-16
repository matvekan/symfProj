<?php

declare(strict_types=1);

namespace App\DTO\Request\Admin\Interest;

use Symfony\Component\Validator\Constraints as Assert;

final class CreateAdminInterestRequestDto
{
    public function __construct(
        #[Assert\NotBlank(message: 'name is required.')]
        public readonly string $name = '',
    ) {
    }
}
