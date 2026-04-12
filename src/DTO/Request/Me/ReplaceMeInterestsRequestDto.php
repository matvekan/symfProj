<?php

declare(strict_types=1);

namespace App\DTO\Request\Me;

use App\Entity\Interest;
use App\Validator\Constraint\EntityExists;
use Symfony\Component\Validator\Constraints as Assert;

final class ReplaceMeInterestsRequestDto
{
    #[Assert\Type('array')]
    #[Assert\All([
        new Assert\Type('integer'),
    ])]
    #[EntityExists(entity: Interest::class)]
    public array $interestIds = [];

    public bool $hasInterestIds = false;

    #[Assert\IsTrue(message: 'interestIds must be provided.')]
    public function hasInterestIdsProvided(): bool
    {
        return $this->hasInterestIds;
    }

    public static function fromArray(array $data): self
    {
        $dto = new self();
        if (array_key_exists('interestIds', $data) || array_key_exists('interest_ids', $data)) {
            $dto->hasInterestIds = true;
            $interestIds = $data['interestIds'] ?? $data['interest_ids'] ?? [];
            $dto->interestIds = is_array($interestIds) ? array_values($interestIds) : [];
        }

        return $dto;
    }
}
