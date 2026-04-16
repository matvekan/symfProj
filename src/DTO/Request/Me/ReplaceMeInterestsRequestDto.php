<?php

declare(strict_types=1);

namespace App\DTO\Request\Me;

use App\Entity\Interest;
use App\Validator\Constraint\EntityExists;
use Symfony\Component\Validator\Constraints as Assert;

final class ReplaceMeInterestsRequestDto
{
    public function __construct(
        #[Assert\NotNull(message: 'interestIds must be provided.')]
        #[Assert\Type('array')]
        #[Assert\All([
            new Assert\Type('integer'),
        ])]
        #[EntityExists(entity: Interest::class)]
        public readonly ?array $interestIds = null,
    ) {
    }
}
