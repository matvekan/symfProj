<?php

declare(strict_types=1);

namespace App\Tests\Integration\Repository;

use App\DTO\Query\Admin\AdminUserFiltersDto;
use App\Entity\Interest;
use App\Entity\User;
use App\Repository\InterestRepository;
use App\Repository\UserRepository;
use App\Tests\Integration\DatabaseTestCase;

final class UserRepositoryTest extends DatabaseTestCase
{
    private UserRepository $userRepository;
    private InterestRepository $interestRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $userRepository = self::getContainer()->get(UserRepository::class);
        \assert($userRepository instanceof UserRepository);
        $this->userRepository = $userRepository;

        $interestRepository = self::getContainer()->get(InterestRepository::class);
        \assert($interestRepository instanceof InterestRepository);
        $this->interestRepository = $interestRepository;
    }

    public function testStoreAndFindOneByEmail(): void
    {
        $user = $this->makeUser('John', 'john@example.com', 'ROLE_USER', new \DateTimeImmutable('-1 day'));
        $this->userRepository->store($user);

        $found = $this->userRepository->findOneByEmail('john@example.com');

        self::assertNotNull($found);
        self::assertSame('John', $found->getName());
    }

    public function testFindByAdminFiltersByRoleAndName(): void
    {
        $this->userRepository->store($this->makeUser('Alice Admin', 'a@example.com', 'ROLE_ADMIN', new \DateTimeImmutable('-2 day')));
        $this->userRepository->store($this->makeUser('Bob User', 'b@example.com', 'ROLE_USER', new \DateTimeImmutable('-1 day')));

        $filters = new AdminUserFiltersDto(name: 'Alice', role: 'role_admin');
        $result = $this->userRepository->findByAdminFilters($filters, 1, 20);

        self::assertCount(1, $result);
        self::assertSame('a@example.com', $result[0]->getEmail());
    }

    public function testFindByAdminFiltersByInterestIdsAndCreatedRange(): void
    {
        $music = $this->interestRepository->store((new Interest())->setName('music'));
        $code = $this->interestRepository->store((new Interest())->setName('code'));

        $first = $this->makeUser('First', 'first@example.com', 'ROLE_USER', new \DateTimeImmutable('2026-01-10 10:00:00'));
        $first->addInterest($music);
        $this->userRepository->store($first);

        $second = $this->makeUser('Second', 'second@example.com', 'ROLE_USER', new \DateTimeImmutable('2026-02-10 10:00:00'));
        $second->addInterest($code);
        $this->userRepository->store($second);

        $filters = new AdminUserFiltersDto(
            createdFrom: new \DateTimeImmutable('2026-01-01 00:00:00'),
            createdTo: new \DateTimeImmutable('2026-01-31 23:59:59'),
            interestIds: [(int) $music->getId()],
        );

        $result = $this->userRepository->findByAdminFilters($filters, 1, 20);

        self::assertCount(1, $result);
        self::assertSame('first@example.com', $result[0]->getEmail());
    }

    public function testFindByAdminFiltersPaginatesAndOrdersByNewestId(): void
    {
        $this->userRepository->store($this->makeUser('U1', 'u1@example.com', 'ROLE_USER', new \DateTimeImmutable()));
        $this->userRepository->store($this->makeUser('U2', 'u2@example.com', 'ROLE_USER', new \DateTimeImmutable()));
        $this->userRepository->store($this->makeUser('U3', 'u3@example.com', 'ROLE_USER', new \DateTimeImmutable()));

        $result = $this->userRepository->findByAdminFilters(new AdminUserFiltersDto(), 1, 2);

        self::assertCount(2, $result);
        self::assertSame('u3@example.com', $result[0]->getEmail());
        self::assertSame('u2@example.com', $result[1]->getEmail());
    }

    private function makeUser(string $name, string $email, string $role, \DateTimeImmutable $createdAt): User
    {
        return (new User())
            ->setName($name)
            ->setEmail($email)
            ->setRole($role)
            ->setPassword('hash')
            ->setCreatedAt($createdAt);
    }
}
