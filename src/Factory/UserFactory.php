<?php

namespace App\Factory;

use App\DTO\Input\User\StoreUserInputDTO;
use App\DTO\Output\Interest\InterestOutputDTO;
use App\DTO\Output\User\UserOutputDTO;
use App\Entity\User;
use App\Repository\InterestRepository;

class UserFactory
{
    public function __construct(private InterestRepository $interestRepository)
    {
    }

    public function makeUser (StoreUserInputDTO $storeUserInputDTO): User
    {
        $user = new User();
        $user->setEmail($storeUserInputDTO->email);
        $user->setName($storeUserInputDTO->name);
        $user->setPassword($storeUserInputDTO->password);
        $user->setRole($storeUserInputDTO->role);
        $user->setCreatedAt($storeUserInputDTO->createdAt);

        $interestIds = array_values(array_unique(array_map('intval', $storeUserInputDTO->interestIds)));
        if ($interestIds !== []) {
            $interests = $this->interestRepository->findBy(['id' => $interestIds]);

            if (count($interests) !== count($interestIds)) {
                throw new \InvalidArgumentException('Some interestIds do not exist.');
            }

            foreach ($interests as $interest) {
                $user->addInterest($interest);
            }
        }

        return $user;
    }

    public function makeStoreUserInputDTO (array $data): StoreUserInputDTO
    {
        $user = new StoreUserInputDTO();
        $user->email=$data['email'] ?? null;
        $user->name=$data['name'] ?? null;
        $user->password=$data['password'] ?? null;
        $user->role=$data['role'] ?? null;
        $user->createdAt = isset($data['created_at']) ? new \DateTimeImmutable($data['created_at']) : null;
        $user->interestIds = $data['interestIds'] ?? $data['interest_ids'] ?? [];
        return $user;
    }

    public function makeStoreUserOutputDTO (User $user): UserOutputDTO
    {
        $userOutputDTO = new UserOutputDTO();
        $userOutputDTO->id=$user->getId();
        $userOutputDTO->email=$user->getEmail();
        $userOutputDTO->name=$user->getName();
        $userOutputDTO->password=null;
        $userOutputDTO->role=$user->getRole();
        $userOutputDTO->createdAt = $user->getCreatedAt();
        foreach ($user->getInterest() as $interest) {
            $interestOutputDTO = new InterestOutputDTO();
            $interestOutputDTO->id = $interest->getId();
            $interestOutputDTO->name = $interest->getName();
            $userOutputDTO->interests[] = $interestOutputDTO;
        }

        return $userOutputDTO;
    }

    public function makeUserOutputDTOs(array $users):array
    {
        return array_map(fn($user) => $this->makeStoreUserOutputDTO($user), $users);
    }

}
