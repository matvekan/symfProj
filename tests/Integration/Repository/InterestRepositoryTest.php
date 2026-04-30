<?php

declare(strict_types=1);

namespace App\Tests\Integration\Repository;

use App\Entity\Interest;
use App\Repository\InterestRepository;
use App\Tests\Integration\DatabaseTestCase;

final class InterestRepositoryTest extends DatabaseTestCase
{
    private InterestRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $repository = self::getContainer()->get(InterestRepository::class);
        \assert($repository instanceof InterestRepository);
        $this->repository = $repository;
    }

    public function testStoreAndFindByName(): void
    {
        $interest = (new Interest())->setName('music');
        $this->repository->store($interest);

        $found = $this->repository->findByName('music');

        self::assertNotNull($found);
        self::assertSame('music', $found->getName());
    }

    public function testFindAllOrderedByName(): void
    {
        $this->repository->store((new Interest())->setName('zeta'));
        $this->repository->store((new Interest())->setName('alpha'));

        $result = $this->repository->findAllOrderedByName();

        self::assertCount(2, $result);
        self::assertSame('alpha', $result[0]->getName());
        self::assertSame('zeta', $result[1]->getName());
    }

    public function testFindByIdsReturnsOnlyRequestedRows(): void
    {
        $first = $this->repository->store((new Interest())->setName('first'));
        $second = $this->repository->store((new Interest())->setName('second'));

        $result = $this->repository->findByIds([(int) $second->getId()]);

        self::assertCount(1, $result);
        self::assertSame((int) $second->getId(), (int) $result[0]->getId());
        self::assertNotSame((int) $first->getId(), (int) $result[0]->getId());
    }

    public function testFindByIdsReturnsEmptyForEmptyInput(): void
    {
        self::assertSame([], $this->repository->findByIds([]));
    }
}
