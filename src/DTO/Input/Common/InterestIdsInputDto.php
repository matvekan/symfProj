<?php

declare(strict_types=1);

namespace App\DTO\Input\Common;

final class InterestIdsInputDto
{
    /**
     * @param array<int, int|string> $interestIds
     */
    public function __construct(public array $interestIds)
    {
    }

    /**
     * @return array<int, int>
     */
    public function normalized(): array
    {
        return array_values(array_unique(array_map(
            static fn (int|string $id): int => (int) $id,
            $this->interestIds,
        )));
    }
}
