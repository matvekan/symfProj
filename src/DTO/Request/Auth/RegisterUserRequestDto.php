<?php

declare(strict_types=1);

namespace App\DTO\Request\Auth;

use App\Entity\Interest;
use App\Validator\Constraint\EntityExists;
use Symfony\Component\Validator\Constraints as Assert;

final class RegisterUserRequestDto
{
    public function __construct(
        #[Assert\NotBlank(message: 'name is required.')]
        #[Assert\Length(min: 3, max: 255)]
        public readonly string $name = '',
        #[Assert\NotBlank(message: 'email is required.')]
        #[Assert\Email(message: 'email is not valid.')]
        public readonly string $email = '',
        #[Assert\NotBlank(message: 'password is required.')]
        public readonly string $password = '',
        #[Assert\Type('array')]
        #[Assert\All([
            new Assert\Type('integer'),
        ])]
        #[EntityExists(entity: Interest::class)]
        public readonly array $interestIds = [],
    ) {
    }
}
