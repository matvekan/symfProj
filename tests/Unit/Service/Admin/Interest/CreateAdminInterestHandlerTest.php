<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Admin\Interest;

use App\DTO\Request\Admin\Interest\CreateAdminInterestRequestDto;
use App\Entity\Interest;
use App\Repository\Contract\InterestRepositoryInterface;
use App\Service\Admin\Interest\CreateAdminInterestHandler;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemPoolInterface;

#[AllowMockObjectsWithoutExpectations]
final class CreateAdminInterestHandlerTest extends TestCase
{
    public function testHandleThrowsForBlankName(): void
    {
        $repo = $this->createMock(InterestRepositoryInterface::class);
        $cache = $this->createMock(CacheItemPoolInterface::class);
        $handler = new CreateAdminInterestHandler($repo, $cache);

        $this->expectException(\InvalidArgumentException::class);
        $handler->handle(new CreateAdminInterestRequestDto('   '));
    }

    public function testHandleThrowsWhenInterestExists(): void
    {
        $repo = $this->createMock(InterestRepositoryInterface::class);
        $cache = $this->createMock(CacheItemPoolInterface::class);
        $repo->method('findByName')->willReturn(new Interest());

        $handler = new CreateAdminInterestHandler($repo, $cache);

        $this->expectException(\DomainException::class);
        $handler->handle(new CreateAdminInterestRequestDto('music'));
    }

    public function testHandleStoresNewInterestAndClearsCache(): void
    {
        $repo = $this->createMock(InterestRepositoryInterface::class);
        $cache = $this->createMock(CacheItemPoolInterface::class);

        $repo->method('findByName')->willReturn(null);
        $repo->expects(self::once())->method('store')->with(self::isInstanceOf(Interest::class));
        $cache->expects(self::once())->method('clear');

        $handler = new CreateAdminInterestHandler($repo, $cache);
        $interest = $handler->handle(new CreateAdminInterestRequestDto('  music  '));

        self::assertSame('music', $interest->getName());
    }
}
