<?php

namespace App\DTO\Output\Interest;

use Symfony\Component\Serializer\Annotation\Groups;

class InterestOutputDTO
{
    #[Groups(groups: ['user:item'])]
    public ?int $id = null;

    #[Groups(groups: ['user:item'])]
    public ?string $name = null;
}
