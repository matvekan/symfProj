<?php

declare(strict_types=1);

namespace App\DTO\Input\User;

use App\Entity\Interest;
use App\Validator\Constraint\EntityExists;
use Symfony\Component\Validator\Constraints as Assert;

final class StoreUserInputDTO
{
    #[Assert\NotBlank(allowNull: null, normalizer: 'trim')]
    #[Assert\Length(min: 3, max: 255)]
    public ?string $name = null;

    #[Assert\NotBlank(allowNull: null, normalizer: 'trim')]
    public ?string $password = null;

    #[Assert\NotBlank(allowNull: null, normalizer: 'trim')]
    public ?string $role = null;

    #[Assert\Type(\DateTimeImmutable::class)]
    public ?\DateTimeImmutable $createdAt = null;

    #[Assert\NotBlank(allowNull: null, normalizer: 'trim')]
    #[Assert\Email(message: 'not valid email')]
    public ?string $email = null;

    #[Assert\Type('array')]
    #[Assert\All([
        new Assert\Type('integer'),
    ])]
    #[EntityExists(entity: Interest::class)]
    public array $interestIds = [];
}
