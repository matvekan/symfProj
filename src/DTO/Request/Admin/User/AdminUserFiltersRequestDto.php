<?php

declare(strict_types=1);

namespace App\DTO\Request\Admin\User;

use App\DTO\Query\Admin\AdminUserFiltersDto;
use App\DTO\Query\Admin\ListAdminUsersQueryDto;
use App\Entity\Interest;
use App\Validator\Constraint\EntityExists;
use Symfony\Component\Validator\Constraints as Assert;

final class AdminUserFiltersRequestDto
{
    /**
     * @param array<int, int> $interestIds
     */
    public function __construct(
        #[Assert\Length(min: 3, max: 255)]
        public readonly ?string $name = null,
        #[Assert\Email(message: 'email is not valid.')]
        public readonly ?string $email = null,
        #[Assert\Choice(choices: ['ROLE_USER', 'ROLE_ADMIN'])]
        public readonly ?string $role = null,
        #[Assert\DateTime]
        public readonly ?string $createdFrom = null,
        #[Assert\DateTime]
        public readonly ?string $createdTo = null,
        public readonly ?string $interestName = null,
        #[Assert\All([
            new Assert\Type('integer'),
        ])]
        #[EntityExists(entity: Interest::class)]
        public readonly array $interestIds = [],
        #[Assert\Positive]
        public readonly int $page = 1,
        #[Assert\Range(min: 1, max: 100)]
        public readonly int $limit = 20,
    ) {
    }

    public function toQueryDto(): ListAdminUsersQueryDto
    {
        return new ListAdminUsersQueryDto(
            filters: new AdminUserFiltersDto(
                name: $this->normalize($this->name),
                email: $this->normalize($this->email),
                role: $this->normalize($this->role),
                createdFrom: $this->toDateTime($this->createdFrom),
                createdTo: $this->toDateTime($this->createdTo),
                interestName: $this->normalize($this->interestName),
                interestIds: array_values(array_unique(array_map('intval', $this->interestIds))),
            ),
            page: max(1, $this->page),
            limit: max(1, min(100, $this->limit)),
        );
    }

    private function normalize(?string $value): ?string
    {
        if (null === $value) {
            return null;
        }

        $trimmed = trim($value);

        return '' === $trimmed ? null : $trimmed;
    }

    private function toDateTime(?string $value): ?\DateTimeImmutable
    {
        $normalized = $this->normalize($value);
        if (null === $normalized) {
            return null;
        }

        return new \DateTimeImmutable($normalized);
    }
}
