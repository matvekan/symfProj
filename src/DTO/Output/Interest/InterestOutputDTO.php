<?php

declare(strict_types=1);

namespace App\DTO\Output\Interest;

use Symfony\Component\Serializer\Annotation\Groups;

final class InterestOutputDTO
{
    #[Groups(groups: ['user:item'])]
    public ?int $id = null;

    #[Groups(groups: ['user:item'])]
    public ?string $name = null;
}
