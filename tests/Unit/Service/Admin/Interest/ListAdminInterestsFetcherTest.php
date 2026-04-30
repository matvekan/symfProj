<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Admin\Interest;

use App\Entity\Interest;
use App\Repository\Contract\InterestRepositoryInterface;
use App\Service\Admin\Interest\ListAdminInterestsFetcher;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;

#[AllowMockObjectsWithoutExpectations]
final class ListAdminInterestsFetcherTest extends TestCase
{
    public function testFetchMapsInterestsToArrayPayload(): void
    {
        $first = (new Interest())->setName('music');
        $second = (new Interest())->setName('programming');

        $repo = $this->createMock(InterestRepositoryInterface::class);
        $repo->method('findAllOrderedByName')->willReturn([$first, $second]);

        $fetcher = new ListAdminInterestsFetcher($repo);
        $payload = $fetcher->fetch();
        /* @var array<int, array{id: int|null, name: string|null}> $payload */

        self::assertCount(2, $payload);
        self::assertSame(['id', 'name'], array_keys($payload[0]));
        self::assertSame('music', $payload[0]['name']);
        self::assertSame('programming', $payload[1]['name']);
    }
}
