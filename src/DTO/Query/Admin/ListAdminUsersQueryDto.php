<?php

declare(strict_types=1);

namespace App\DTO\Query\Admin;

final class ListAdminUsersQueryDto
{
    public function __construct(
        public AdminUserFiltersDto $filters,
        public int $page,
        public int $limit,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toCachePayload(): array
    {
        return [
            'filters' => $this->filters->toCachePayload(),
            'page' => $this->page,
            'limit' => $this->limit,
        ];
    }
}
