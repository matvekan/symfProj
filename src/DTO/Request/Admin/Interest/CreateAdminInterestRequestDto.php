<?php

declare(strict_types=1);

namespace App\DTO\Request\Admin\Interest;

use Symfony\Component\Validator\Constraints as Assert;

final class CreateAdminInterestRequestDto
{
    #[Assert\NotBlank(message: 'name is required.')]
    public ?string $name = null;

    public static function fromArray(array $data): self
    {
        $dto = new self();
        $dto->name = array_key_exists('name', $data) ? (string) $data['name'] : null;

        return $dto;
    }
}
