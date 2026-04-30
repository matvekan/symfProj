<?php

declare(strict_types=1);

namespace App\Tests\Functional\Api;

use App\Entity\Interest;
use App\Entity\PasswordReset;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

abstract class ApiWebTestCase extends WebTestCase
{
    protected KernelBrowser $client;
    protected EntityManagerInterface $em;
    protected UserPasswordHasherInterface $hasher;

    protected function setUp(): void
    {
        self::ensureKernelShutdown();
        $this->client = static::createClient();
        $container = $this->client->getContainer();

        $em = $container->get(EntityManagerInterface::class);
        \assert($em instanceof EntityManagerInterface);
        $this->em = $em;

        $hasher = $container->get(UserPasswordHasherInterface::class);
        \assert($hasher instanceof UserPasswordHasherInterface);
        $this->hasher = $hasher;
        $this->truncateDatabase();
    }

    protected function tearDown(): void
    {
        $this->truncateDatabase();
        $this->em->close();
        parent::tearDown();
    }

    protected function createUser(string $email, string $password, string $role = 'ROLE_USER', string $name = 'User'): User
    {
        $user = (new User())
            ->setEmail($email)
            ->setName($name)
            ->setRole($role)
            ->setCreatedAt(new \DateTimeImmutable());

        $user->setPassword($this->hasher->hashPassword($user, $password));

        $this->em->persist($user);
        $this->em->flush();

        return $user;
    }

    protected function createInterest(string $name): Interest
    {
        $interest = (new Interest())->setName($name);
        $this->em->persist($interest);
        $this->em->flush();

        return $interest;
    }

    protected function createPasswordReset(string $email, string $token, bool $expired = false): PasswordReset
    {
        $reset = (new PasswordReset())
            ->setEmail($email)
            ->setToken($token)
            ->setCreatedAt(new \DateTimeImmutable('-5 minutes'))
            ->setExpiresAt($expired ? new \DateTimeImmutable('-1 minute') : new \DateTimeImmutable('+30 minutes'));

        $this->em->persist($reset);
        $this->em->flush();

        return $reset;
    }

    protected function loginAndGetToken(KernelBrowser $client, string $email, string $password): string
    {
        $client->jsonRequest('POST', '/api/login_check', [
            'email' => $email,
            'password' => $password,
        ]);

        self::assertResponseIsSuccessful();
        $data = json_decode((string) $client->getResponse()->getContent(), true, 512, \JSON_THROW_ON_ERROR);
        \assert(\is_array($data));

        $token = $data['token'] ?? '';
        \assert(\is_string($token));

        return $token;
    }

    /** @return array<string, string> */
    protected function authHeaders(string $token): array
    {
        return ['HTTP_AUTHORIZATION' => 'Bearer '.$token];
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
