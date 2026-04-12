<?php

declare(strict_types=1);

namespace App\DTO\Request\Auth;

use Symfony\Component\Validator\Constraints as Assert;

final class ResetPasswordRequestDto
{
    #[Assert\NotBlank(message: 'token is required.')]
    public ?string $token = null;

    #[Assert\NotBlank(message: 'newPassword is required.')]
    public ?string $newPassword = null;

    public static function fromArray(array $data): self
    {
        $dto = new self();
        $dto->token = array_key_exists('token', $data) ? (string) $data['token'] : null;
        $dto->newPassword = array_key_exists('newPassword', $data) ? (string) $data['newPassword'] : null;

        return $dto;
    }
}
