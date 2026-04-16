<?php

declare(strict_types=1);

namespace App\DTO\Input\User;

use App\Entity\Interest;
use App\Validator\Constraint\EntityExists;
use Symfony\Component\Validator\Constraints as Assert;

final class StoreUserInputDTO
{
    /**
     * @param array<int, int> $interestIds
     */
    public function __construct(
        #[Assert\NotBlank(allowNull: null, normalizer: 'trim')]
        #[Assert\Length(min: 3, max: 255)]
        public readonly ?string $name = null,
        #[Assert\NotBlank(allowNull: null, normalizer: 'trim')]
        public readonly ?string $password = null,
        #[Assert\NotBlank(allowNull: null, normalizer: 'trim')]
        public readonly ?string $role = null,
        #[Assert\Type(\DateTimeImmutable::class)]
        public readonly ?\DateTimeImmutable $createdAt = null,
        #[Assert\NotBlank(allowNull: null, normalizer: 'trim')]
        #[Assert\Email(message: 'not valid email')]
        public readonly ?string $email = null,
        #[Assert\Type('array')]
        #[Assert\All([
            new Assert\Type('integer'),
        ])]
        #[EntityExists(entity: Interest::class)]
        public readonly array $interestIds = [],
    ) {
    }
}
