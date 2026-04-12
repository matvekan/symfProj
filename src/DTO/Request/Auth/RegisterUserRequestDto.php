<?php

declare(strict_types=1);

namespace App\DTO\Request\Auth;

use App\Entity\Interest;
use App\Validator\Constraint\EntityExists;
use Symfony\Component\Validator\Constraints as Assert;

final class RegisterUserRequestDto
{
    #[Assert\NotBlank(message: 'name is required.')]
    #[Assert\Length(min: 3, max: 255)]
    public ?string $name = null;

    #[Assert\NotBlank(message: 'email is required.')]
    #[Assert\Email(message: 'email is not valid.')]
    public ?string $email = null;

    #[Assert\NotBlank(message: 'password is required.')]
    public ?string $password = null;

    #[Assert\Type('array')]
    #[Assert\All([
        new Assert\Type('integer'),
    ])]
    #[EntityExists(entity: Interest::class)]
    public array $interestIds = [];

    public static function fromArray(array $data): self
    {
        $dto = new self();
        $dto->name = array_key_exists('name', $data) ? (string) $data['name'] : null;
        $dto->email = array_key_exists('email', $data) ? (string) $data['email'] : null;
        $dto->password = array_key_exists('password', $data) ? (string) $data['password'] : null;

        $interestIds = $data['interestIds'] ?? $data['interest_ids'] ?? [];
        $dto->interestIds = is_array($interestIds) ? array_values($interestIds) : [];

        return $dto;
    }
}
