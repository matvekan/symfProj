<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Admin\User;

use App\DTO\Query\Admin\AdminUserFiltersDto;
use App\DTO\Query\Admin\ListAdminUsersQueryDto;
use App\Entity\User;
use App\Factory\UserFactory;
use App\Repository\Contract\InterestRepositoryInterface;
use App\Repository\Contract\UserRepositoryInterface;
use App\Service\Admin\User\ListAdminUsersFetcher;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Serializer\SerializerInterface;

#[AllowMockObjectsWithoutExpectations]
final class ListAdminUsersFetcherTest extends TestCase
{
    public function testFetchReturnsCachedPayloadOnHit(): void
    {
        $repo = $this->createMock(UserRepositoryInterface::class);
        $interestRepository = $this->createMock(InterestRepositoryInterface::class);
        $userFactory = new UserFactory($interestRepository);
        $cachePool = $this->createMock(CacheItemPoolInterface::class);
        $serializer = $this->createMock(SerializerInterface::class);
        $cacheItem = $this->createMock(CacheItemInterface::class);

        $cachePool->method('getItem')->willReturn($cacheItem);
        $cacheItem->method('isHit')->willReturn(true);
        $cacheItem->method('get')->willReturn('{"cached":true}');

        $repo->expects(self::never())->method('findByAdminFilters');
        $serializer->expects(self::never())->method('serialize');

        $fetcher = new ListAdminUsersFetcher($repo, $userFactory, $cachePool, $serializer);
        $result = $fetcher->fetch(new ListAdminUsersQueryDto(new AdminUserFiltersDto(), 1, 20));

        self::assertSame('{"cached":true}', $result);
    }

    public function testFetchBuildsPayloadAndStoresInCacheOnMiss(): void
    {
        $user = (new User())
            ->setName('John')
            ->setEmail('john@example.com')
            ->setPassword('hash')
            ->setRole('ROLE_USER')
            ->setCreatedAt(new \DateTimeImmutable());

        $repo = $this->createMock(UserRepositoryInterface::class);
        $interestRepository = $this->createMock(InterestRepositoryInterface::class);
        $userFactory = new UserFactory($interestRepository);
        $cachePool = $this->createMock(CacheItemPoolInterface::class);
        $serializer = $this->createMock(SerializerInterface::class);
        $cacheItem = $this->createMock(CacheItemInterface::class);

        $cachePool->method('getItem')->willReturn($cacheItem);
        $cacheItem->method('isHit')->willReturn(false);
        $cacheItem->expects(self::once())->method('set')->with('{"data":1}')->willReturnSelf();
        $cacheItem->expects(self::once())->method('expiresAfter')->with(300)->willReturnSelf();
        $cachePool->expects(self::once())->method('save')->with($cacheItem);

        $repo->method('findByAdminFilters')->willReturn([$user]);
        $serializer->method('serialize')->willReturn('{"data":1}');

        $fetcher = new ListAdminUsersFetcher($repo, $userFactory, $cachePool, $serializer);
        $result = $fetcher->fetch(new ListAdminUsersQueryDto(new AdminUserFiltersDto(), 1, 20));

        self::assertSame('{"data":1}', $result);
    }
}
