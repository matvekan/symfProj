<?php

declare(strict_types=1);

namespace App\Service\Admin\Interest;

use App\Entity\Interest;
use App\Repository\Contract\InterestRepositoryInterface;

final class ListAdminInterestsFetcher
{
    public function __construct(private InterestRepositoryInterface $interestRepository)
    {
    }

    /**
     * @return array<int, array{id: int|null, name: string|null}>
     */
    public function fetch(): array
    {
        $interests = $this->interestRepository->findAllOrderedByName();

        return array_map(static fn (Interest $interest) => [
            'id' => $interest->getId(),
            'name' => $interest->getName(),
        ], $interests);
    }
}
