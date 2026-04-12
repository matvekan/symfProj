<?php

declare(strict_types=1);

namespace App\DTO\Output\User;

use App\DTO\Output\Interest\InterestOutputDTO;
use Symfony\Component\Serializer\Annotation\Groups;

final class UserOutputDTO
{
    #[Groups(groups: ['user:item'])]
    public ?int $id = null;

    #[Groups(groups: ['user:item'])]
    public ?string $name = null;

    #[Groups(groups: ['user:item'])]
    public ?string $password = null;

    #[Groups(groups: ['user:item'])]
    public ?string $role = null;

    #[Groups(groups: ['user:item'])]
    public ?\DateTimeImmutable $createdAt = null;

    #[Groups(groups: ['user:item'])]
    public ?string $email = null;

    #[Groups(groups: ['user:item'])]
    public array $interests = [];
}
