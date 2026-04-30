<?php

declare(strict_types=1);

namespace App\DTO\Query\Admin;

final class AdminUserFiltersDto
{
    /**
     * @param array<int, int> $interestIds
     */
    public function __construct(
        public ?string $name = null,
        public ?string $email = null,
        public ?string $role = null,
        public ?\DateTimeImmutable $createdFrom = null,
        public ?\DateTimeImmutable $createdTo = null,
        public ?string $interestName = null,
        public array $interestIds = [],
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toCachePayload(): array
    {
        return [
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role,
            'createdFrom' => $this->createdFrom?->format(\DATE_ATOM),
            'createdTo' => $this->createdTo?->format(\DATE_ATOM),
            'interestName' => $this->interestName,
            'interestIds' => $this->interestIds,
        ];
    }
}
