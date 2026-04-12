<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\Input\Common\InterestIdsInputDto;
use App\DTO\Input\User\StoreUserInputDTO;
use App\DTO\Input\User\UpdateUserInputDto;
use App\Entity\User;
use App\Factory\UserFactory;
use App\Repository\Contract\InterestRepositoryInterface;
use App\Repository\Contract\UserRepositoryInterface;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserService
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private UserFactory $userFactory,
        private UserPasswordHasherInterface $passwordHasher,
        private InterestRepositoryInterface $interestRepository,
        private CacheItemPoolInterface $cachePool,
    )
    {
    }

    public function store(StoreUserInputDTO $storeUserInputDTO): User
    {
        $user = $this->userFactory->makeUser($storeUserInputDTO);
        $user->setPassword($this->passwordHasher->hashPassword($user, $user->getPassword() ?? ''));
        if ($user->getCreatedAt() === null) {
            $user->setCreatedAt(new \DateTimeImmutable());
        }
        if ($user->getRole() === null || $user->getRole() === '') {
            $user->setRole('ROLE_USER');
        }

        $this->cachePool->clear();

        return $this->userRepository->store($user);
    }

    public function updateUser(User $user, UpdateUserInputDto $updateUserInputDto): User
    {
        if ($updateUserInputDto->name !== null) {
            $user->setName($updateUserInputDto->name);
        }

        if ($updateUserInputDto->email !== null) {
            $user->setEmail($updateUserInputDto->email);
        }

        if ($updateUserInputDto->role !== null) {
            $user->setRole($updateUserInputDto->role);
        }

        if ($updateUserInputDto->password !== null && $updateUserInputDto->password !== '') {
            $user->setPassword($this->passwordHasher->hashPassword($user, $updateUserInputDto->password));
        }

        if ($updateUserInputDto->hasInterestIds) {
            $this->replaceInterests($user, new InterestIdsInputDto($updateUserInputDto->interestIds));
        }

        $this->cachePool->clear();

        return $this->userRepository->store($user);
    }

    public function replaceInterests(User $user, InterestIdsInputDto $interestIdsInputDto): User
    {
        foreach ($user->getInterest()->toArray() as $interest) {
            $user->removeInterest($interest);
        }

        $normalizedIds = $interestIdsInputDto->normalized();
        if ($normalizedIds !== []) {
            $interests = $this->interestRepository->findByIds($normalizedIds);
            foreach ($interests as $interest) {
                $user->addInterest($interest);
            }
        }

        $this->cachePool->clear();

        return $this->userRepository->store($user);
    }
}
