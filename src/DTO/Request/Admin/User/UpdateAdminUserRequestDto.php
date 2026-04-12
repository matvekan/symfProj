<?php

declare(strict_types=1);

namespace App\DTO\Request\Admin\User;

use App\DTO\Input\User\UpdateUserInputDto;
use App\Entity\Interest;
use App\Validator\Constraint\EntityExists;
use Symfony\Component\Validator\Constraints as Assert;

final class UpdateAdminUserRequestDto
{
    #[Assert\Length(min: 3, max: 255)]
    public ?string $name = null;

    #[Assert\Email(message: 'email is not valid.')]
    public ?string $email = null;

    #[Assert\Choice(choices: ['ROLE_USER', 'ROLE_ADMIN'])]
    public ?string $role = null;

    public ?string $password = null;

    #[Assert\Type('array')]
    #[Assert\All([
        new Assert\Type('integer'),
    ])]
    #[EntityExists(entity: Interest::class)]
    public array $interestIds = [];

    public bool $hasInterestIds = false;

    public static function fromArray(array $data): self
    {
        $dto = new self();
        $dto->name = array_key_exists('name', $data) ? (string) $data['name'] : null;
        $dto->email = array_key_exists('email', $data) ? (string) $data['email'] : null;
        $dto->role = array_key_exists('role', $data) ? (string) $data['role'] : null;
        $dto->password = array_key_exists('password', $data) ? (string) $data['password'] : null;

        if (array_key_exists('interestIds', $data) || array_key_exists('interest_ids', $data)) {
            $dto->hasInterestIds = true;
            $interestIds = $data['interestIds'] ?? $data['interest_ids'] ?? [];
            $dto->interestIds = is_array($interestIds) ? array_values($interestIds) : [];
        }

        return $dto;
    }

    public function toUpdateUserInputDto(): UpdateUserInputDto
    {
        $dto = new UpdateUserInputDto();
        $dto->name = $this->name;
        $dto->email = $this->email;
        $dto->role = $this->role;
        $dto->password = $this->password;
        $dto->hasInterestIds = $this->hasInterestIds;
        $dto->interestIds = $this->interestIds;

        return $dto;
    }
}
