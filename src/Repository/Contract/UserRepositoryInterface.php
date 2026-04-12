<?php

declare(strict_types=1);

namespace App\Repository\Contract;

use App\DTO\Query\Admin\AdminUserFiltersDto;
use App\Entity\User;

interface UserRepositoryInterface
{
    public function store(User $user, bool $isFlush = true): User;

    public function remove(User $user, bool $isFlush = true): void;

    public function findOneByEmail(string $email): ?User;

    /**
     * @return array<int, User>
     */
    public function findByAdminFilters(AdminUserFiltersDto $filters, int $page = 1, int $limit = 20): array;

}
