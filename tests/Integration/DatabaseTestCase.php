<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

abstract class DatabaseTestCase extends KernelTestCase
{
    protected EntityManagerInterface $em;

    protected function setUp(): void
    {
        self::bootKernel();
        $em = self::getContainer()->get(EntityManagerInterface::class);
        \assert($em instanceof EntityManagerInterface);
        $this->em = $em;
        $this->truncateDatabase();
    }

    protected function tearDown(): void
    {
        $this->truncateDatabase();
        $this->em->close();
        parent::tearDown();
    }

    private function truncateDatabase(): void
    {
        $connection = $this->em->getConnection();
        $schemaManager = $connection->createSchemaManager();
        $tables = $schemaManager->listTableNames();

        $connection->executeStatement('SET FOREIGN_KEY_CHECKS = 0');
        foreach ($tables as $table) {
            if ('doctrine_migration_versions' === $table) {
                continue;
            }

            $connection->executeStatement(\sprintf('TRUNCATE TABLE `%s`', $table));
        }
        $connection->executeStatement('SET FOREIGN_KEY_CHECKS = 1');
    }
}
