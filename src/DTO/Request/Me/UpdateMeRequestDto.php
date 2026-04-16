<?php

declare(strict_types=1);

namespace App\DTO\Request\Me;

use App\DTO\Input\User\UpdateUserInputDto;
use App\Entity\Interest;
use App\Validator\Constraint\EntityExists;
use Symfony\Component\Validator\Constraints as Assert;

final class UpdateMeRequestDto
{
    public function __construct(
        #[Assert\Length(min: 3, max: 255)]
        public readonly ?string $name = null,
        #[Assert\Email(message: 'email is not valid.')]
        public readonly ?string $email = null,
        public readonly ?string $password = null,
        #[Assert\Type('array')]
        #[Assert\All([
            new Assert\Type('integer'),
        ])]
        #[EntityExists(entity: Interest::class)]
        public readonly ?array $interestIds = null,
    ) {
    }

    public function toUpdateUserInputDto(): UpdateUserInputDto
    {
        return new UpdateUserInputDto(
            name: $this->name,
            email: $this->email,
            password: $this->password,
            hasInterestIds: $this->interestIds !== null,
            interestIds: $this->interestIds ?? [],
        );
    }
}
