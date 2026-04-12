<?php

declare(strict_types=1);

namespace App\Repository\Contract;

use App\Entity\Interest;

interface InterestRepositoryInterface
{
    public function store(Interest $interest, bool $isFlush = true): Interest;

    public function findByName(string $name): ?Interest;

    /**
     * @return array<int, Interest>
     */
    public function findAllOrderedByName(): array;

    /**
     * @param array<int, int> $ids
     *
     * @return array<int, Interest>
     */
    public function findByIds(array $ids): array;
}
