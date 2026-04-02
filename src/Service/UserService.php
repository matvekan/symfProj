<?php

namespace App\Service;

use App\DTO\Input\User\StoreUserInputDTO;
use App\Entity\User;
use App\Factory\UserFactory;
use App\Repository\InterestRepository;
use App\Repository\UserRepository;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserService
{
    public function __construct(
        private UserRepository $userRepository,
        private UserFactory $userFactory,
        private UserPasswordHasherInterface $passwordHasher,
        private InterestRepository $interestRepository,
        private CacheItemPoolInterface $cachePool,
    )
    {
    }

    public function store(StoreUserInputDTO $storeUserInputDTO):User
    {

        $user=$this->userFactory->makeUser($storeUserInputDTO);
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

    public function index():array
    {
        return $this->userRepository->findAll();
    }

    public function updateUser(User $user, array $data): User
    {
        if (array_key_exists('name', $data)) {
            $user->setName((string) $data['name']);
        }

        if (array_key_exists('email', $data)) {
            $user->setEmail((string) $data['email']);
        }

        if (array_key_exists('role', $data)) {
            $user->setRole((string) $data['role']);
        }

        if (array_key_exists('password', $data) && is_string($data['password']) && $data['password'] !== '') {
            $user->setPassword($this->passwordHasher->hashPassword($user, $data['password']));
        }

        if (array_key_exists('interestIds', $data) || array_key_exists('interest_ids', $data)) {
            $ids = $data['interestIds'] ?? $data['interest_ids'] ?? [];
            $this->replaceInterests($user, is_array($ids) ? $ids : []);
        }

        $this->cachePool->clear();

        return $this->userRepository->store($user);
    }

    public function replaceInterests(User $user, array $interestIds): User
    {
        foreach ($user->getInterest()->toArray() as $interest) {
            $user->removeInterest($interest);
        }

        $normalizedIds = array_values(array_unique(array_map('intval', $interestIds)));
        if ($normalizedIds !== []) {
            $interests = $this->interestRepository->findBy(['id' => $normalizedIds]);
            foreach ($interests as $interest) {
                $user->addInterest($interest);
            }
        }

        $this->cachePool->clear();

        return $this->userRepository->store($user);
    }
}
