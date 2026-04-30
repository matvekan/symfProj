<?php

declare(strict_types=1);

namespace App\Tests\Integration\Repository;

use App\Entity\PasswordReset;
use App\Repository\PasswordResetRepository;
use App\Tests\Integration\DatabaseTestCase;

final class PasswordResetRepositoryTest extends DatabaseTestCase
{
    private PasswordResetRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $repository = self::getContainer()->get(PasswordResetRepository::class);
        \assert($repository instanceof PasswordResetRepository);
        $this->repository = $repository;
    }

    public function testFindValidByTokenReturnsRecordWhenNotExpiredAndUnused(): void
    {
        $reset = (new PasswordReset())
            ->setEmail('john@example.com')
            ->setToken('valid-token')
            ->setCreatedAt(new \DateTimeImmutable('-5 minutes'))
            ->setExpiresAt(new \DateTimeImmutable('+30 minutes'));
        $this->repository->store($reset);

        $found = $this->repository->findValidByToken('valid-token');

        self::assertNotNull($found);
        self::assertSame('valid-token', $found->getToken());
    }

    public function testFindValidByTokenReturnsNullForExpiredToken(): void
    {
        $expired = (new PasswordReset())
            ->setEmail('john@example.com')
            ->setToken('expired-token')
            ->setCreatedAt(new \DateTimeImmutable('-2 hours'))
            ->setExpiresAt(new \DateTimeImmutable('-1 hour'));
        $this->repository->store($expired);

        self::assertNull($this->repository->findValidByToken('expired-token'));
    }

    public function testFindValidByTokenReturnsNullForUsedToken(): void
    {
        $used = (new PasswordReset())
            ->setEmail('john@example.com')
            ->setToken('used-token')
            ->setCreatedAt(new \DateTimeImmutable('-5 minutes'))
            ->setExpiresAt(new \DateTimeImmutable('+30 minutes'))
            ->setUsedAt(new \DateTimeImmutable('-1 minute'));
        $this->repository->store($used);

        self::assertNull($this->repository->findValidByToken('used-token'));
    }
}
