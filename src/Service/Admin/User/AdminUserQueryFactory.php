<?php

declare(strict_types=1);

namespace App\Service\Admin\User;

use App\DTO\Query\Admin\AdminUserFiltersDto;
use App\DTO\Query\Admin\ListAdminUsersQueryDto;
use Symfony\Component\HttpFoundation\Request;

final class AdminUserQueryFactory
{
    public function fromRequest(Request $request): ListAdminUsersQueryDto
    {
        $filters = new AdminUserFiltersDto();
        $filters->name = $this->normalizeString($request->query->get('name'));
        $filters->email = $this->normalizeString($request->query->get('email'));
        $filters->role = $this->normalizeString($request->query->get('role'));
        $filters->interestName = $this->normalizeString($request->query->get('interestName'));
        $filters->createdFrom = $this->normalizeDateTime($request->query->get('createdFrom'));
        $filters->createdTo = $this->normalizeDateTime($request->query->get('createdTo'));
        $filters->interestIds = $this->normalizeIds($request->query->all('interestIds'));

        $page = max(1, (int) $request->query->get('page', 1));
        $limit = max(1, min(100, (int) $request->query->get('limit', 20)));

        return new ListAdminUsersQueryDto($filters, $page, $limit);
    }

    private function normalizeString(mixed $value): ?string
    {
        if (!is_string($value)) {
            return null;
        }

        $trimmed = trim($value);

        return $trimmed === '' ? null : $trimmed;
    }

    private function normalizeDateTime(mixed $value): ?\DateTimeImmutable
    {
        if (!is_string($value) || trim($value) === '') {
            return null;
        }

        try {
            return new \DateTimeImmutable($value);
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * @param array<int, mixed> $values
     *
     * @return array<int, int>
     */
    private function normalizeIds(array $values): array
    {
        $result = [];
        foreach ($values as $value) {
            if (is_numeric($value)) {
                $result[] = (int) $value;
            }
        }

        return array_values(array_unique($result));
    }
}
