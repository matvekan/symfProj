<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\DTO\Input\Common\InterestIdsInputDto;
use App\DTO\Input\User\StoreUserInputDTO;
use App\DTO\Input\User\UpdateUserInputDto;
use App\Entity\Interest;
use App\Entity\User;
use App\Factory\UserFactory;
use App\Repository\Contract\InterestRepositoryInterface;
use App\Repository\Contract\UserRepositoryInterface;
use App\Service\UserService;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AllowMockObjectsWithoutExpectations]
final class UserServiceTest extends TestCase
{
    public function testStoreHashesPasswordAndPersistsUser(): void
    {
        $userRepository = $this->createMock(UserRepositoryInterface::class);
        $interestRepository = $this->createMock(InterestRepositoryInterface::class);
        $userFactory = new UserFactory($interestRepository);
        $hasher = $this->createMock(UserPasswordHasherInterface::class);
        $cache = $this->createMock(CacheItemPoolInterface::class);

        $hasher->method('hashPassword')->willReturn('hashed');
        $cache->expects(self::once())->method('clear');
        $userRepository
            ->expects(self::once())
            ->method('store')
            ->with(self::isInstanceOf(User::class))
            ->willReturnCallback(static fn (User $u) => $u);

        $service = new UserService($userRepository, $userFactory, $hasher, $interestRepository, $cache);
        $result = $service->store(new StoreUserInputDTO(
            name: 'John',
            email: 'john@example.com',
            password: 'plain',
            role: 'ROLE_USER',
            createdAt: new \DateTimeImmutable(),
            interestIds: [],
        ));

        self::assertSame('john@example.com', $result->getEmail());
        self::assertSame('hashed', $result->getPassword());
    }

    public function testUpdateUserUpdatesProvidedFieldsAndInterests(): void
    {
        $user = (new User())
            ->setName('Old')
            ->setEmail('old@example.com')
            ->setRole('ROLE_USER')
            ->setPassword('oldhash')
            ->setCreatedAt(new \DateTimeImmutable());

        $existing = (new Interest())->setName('old-interest');
        $newInterest = (new Interest())->setName('new-interest');
        $user->addInterest($existing);

        $userRepository = $this->createMock(UserRepositoryInterface::class);
        $interestRepository = $this->createMock(InterestRepositoryInterface::class);
        $userFactory = new UserFactory($interestRepository);
        $hasher = $this->createMock(UserPasswordHasherInterface::class);
        $cache = $this->createMock(CacheItemPoolInterface::class);

        $hasher->method('hashPassword')->willReturn('new-hash');
        $interestRepository->expects(self::once())->method('findByIds')->with([2])->willReturn([$newInterest]);
        $cache->expects(self::exactly(2))->method('clear');
        $userRepository->expects(self::exactly(2))->method('store')->willReturnCallback(static fn (User $u) => $u);

        $service = new UserService($userRepository, $userFactory, $hasher, $interestRepository, $cache);
        $result = $service->updateUser($user, new UpdateUserInputDto(
            name: 'New Name',
            email: 'new@example.com',
            role: 'ROLE_ADMIN',
            password: 'new-pass',
            hasInterestIds: true,
            interestIds: [2],
        ));

        self::assertSame($user, $result);
        self::assertSame('New Name', $user->getName());
        self::assertSame('new@example.com', $user->getEmail());
        self::assertSame('ROLE_ADMIN', $user->getRole());
        self::assertSame('new-hash', $user->getPassword());
        self::assertCount(1, $user->getInterest());
        $firstInterest = $user->getInterest()->first();
        self::assertInstanceOf(Interest::class, $firstInterest);
        self::assertSame('new-interest', $firstInterest->getName());
    }

    public function testReplaceInterestsRemovesOldAndSavesNewOnes(): void
    {
        $user = (new User())
            ->setName('John')
            ->setEmail('john@example.com')
            ->setRole('ROLE_USER')
            ->setPassword('hash')
            ->setCreatedAt(new \DateTimeImmutable());

        $old = (new Interest())->setName('old');
        $new = (new Interest())->setName('new');
        $user->addInterest($old);

        $userRepository = $this->createMock(UserRepositoryInterface::class);
        $interestRepository = $this->createMock(InterestRepositoryInterface::class);
        $userFactory = new UserFactory($interestRepository);
        $hasher = $this->createMock(UserPasswordHasherInterface::class);
        $cache = $this->createMock(CacheItemPoolInterface::class);

        $interestRepository->expects(self::once())->method('findByIds')->with([5])->willReturn([$new]);
        $cache->expects(self::once())->method('clear');
        $userRepository->expects(self::once())->method('store')->with($user)->willReturn($user);

        $service = new UserService($userRepository, $userFactory, $hasher, $interestRepository, $cache);
        $service->replaceInterests($user, new InterestIdsInputDto([5]));

        self::assertCount(1, $user->getInterest());
        $firstInterest = $user->getInterest()->first();
        self::assertInstanceOf(Interest::class, $firstInterest);
        self::assertSame('new', $firstInterest->getName());
    }
}
