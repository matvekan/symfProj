<?php

declare(strict_types=1);

namespace App\DTO\Request\Auth;

use Symfony\Component\Validator\Constraints as Assert;

final class ForgotPasswordRequestDto
{
    #[Assert\NotBlank(message: 'Email is required.')]
    #[Assert\Email(message: 'email is not valid.')]
    public ?string $email = null;

    public static function fromArray(array $data): self
    {
        $dto = new self();
        $dto->email = array_key_exists('email', $data) ? (string) $data['email'] : null;

        return $dto;
    }
}
