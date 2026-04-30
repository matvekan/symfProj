<?php

declare(strict_types=1);

namespace App\Factory;

use App\DTO\Input\User\StoreUserInputDTO;
use App\DTO\Output\Interest\InterestOutputDTO;
use App\DTO\Output\User\UserOutputDTO;
use App\Entity\User;
use App\Repository\Contract\InterestRepositoryInterface;

final class UserFactory
{
    public function __construct(private InterestRepositoryInterface $interestRepository)
    {
    }

    public function makeUser(StoreUserInputDTO $storeUserInputDTO): User
    {
        if (
            null === $storeUserInputDTO->email
            || null === $storeUserInputDTO->name
            || null === $storeUserInputDTO->password
            || null === $storeUserInputDTO->role
        ) {
            throw new \InvalidArgumentException('Required user fields are missing.');
        }

        $user = new User();
        $user->setEmail($storeUserInputDTO->email);
        $user->setName($storeUserInputDTO->name);
        $user->setPassword($storeUserInputDTO->password);
        $user->setRole($storeUserInputDTO->role);
        $user->setCreatedAt($storeUserInputDTO->createdAt ?? new \DateTimeImmutable());

        $interestIds = array_values(array_unique(array_map('intval', $storeUserInputDTO->interestIds)));
        if ([] !== $interestIds) {
            $interests = $this->interestRepository->findByIds($interestIds);

            if (\count($interests) !== \count($interestIds)) {
                throw new \InvalidArgumentException('Some interestIds do not exist.');
            }

            foreach ($interests as $interest) {
                $user->addInterest($interest);
            }
        }

        return $user;
    }

    public function makeStoreUserOutputDTO(User $user): UserOutputDTO
    {
        $userOutputDTO = new UserOutputDTO();
        $userOutputDTO->id = $user->getId();
        $userOutputDTO->email = $user->getEmail();
        $userOutputDTO->name = $user->getName();
        $userOutputDTO->password = null;
        $userOutputDTO->role = $user->getRole();
        $userOutputDTO->createdAt = $user->getCreatedAt();

        foreach ($user->getInterest() as $interest) {
            $interestOutputDTO = new InterestOutputDTO();
            $interestOutputDTO->id = $interest->getId();
            $interestOutputDTO->name = $interest->getName();
            $userOutputDTO->interests[] = $interestOutputDTO;
        }

        return $userOutputDTO;
    }

    /**
     * @param array<int, User> $users
     *
     * @return array<int, UserOutputDTO>
     */
    public function makeUserOutputDTOs(array $users): array
    {
        return array_map(fn (User $user): UserOutputDTO => $this->makeStoreUserOutputDTO($user), $users);
    }
}
