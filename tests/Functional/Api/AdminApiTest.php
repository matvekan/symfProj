<?php

declare(strict_types=1);

namespace App\Tests\Functional\Api;

final class AdminApiTest extends ApiWebTestCase
{
    public function testAdminEndpointsAreForbiddenForRegularUser(): void
    {
        $this->createUser('user@example.com', 'secret123', 'ROLE_USER');
        $token = $this->loginAndGetToken($this->client, 'user@example.com', 'secret123');

        $this->client->request('GET', '/api/admin/interests', server: $this->authHeaders($token));

        self::assertResponseStatusCodeSame(403);
    }

    public function testAdminCanListAndCreateInterests(): void
    {
        $this->createInterest('music');
        $this->createUser('admin@example.com', 'admin123', 'ROLE_ADMIN', 'Admin');
        $token = $this->loginAndGetToken($this->client, 'admin@example.com', 'admin123');

        $this->client->request('GET', '/api/admin/interests', server: $this->authHeaders($token));
        self::assertResponseIsSuccessful();

        $this->client->jsonRequest('POST', '/api/admin/interests', ['name' => 'programming'], $this->authHeaders($token));
        self::assertResponseStatusCodeSame(201);

        $this->client->jsonRequest('POST', '/api/admin/interests', ['name' => 'programming'], $this->authHeaders($token));
        self::assertResponseStatusCodeSame(409);
    }

    public function testAdminCanListShowAndUpdateUsers(): void
    {
        $this->createUser('admin@example.com', 'admin123', 'ROLE_ADMIN', 'Admin');
        $target = $this->createUser('user@example.com', 'user123', 'ROLE_USER', 'User');
        $token = $this->loginAndGetToken($this->client, 'admin@example.com', 'admin123');

        $this->client->request('GET', '/api/admin/users', server: $this->authHeaders($token));
        self::assertResponseIsSuccessful();
        $list = json_decode((string) $this->client->getResponse()->getContent(), true, 512, \JSON_THROW_ON_ERROR);
        \assert(\is_array($list));
        self::assertGreaterThanOrEqual(2, \count($list));

        $this->client->request('GET', '/api/admin/users/'.$target->getId(), server: $this->authHeaders($token));
        self::assertResponseIsSuccessful();
        $item = json_decode((string) $this->client->getResponse()->getContent(), true, 512, \JSON_THROW_ON_ERROR);
        \assert(\is_array($item));
        self::assertSame('user@example.com', $item['email']);

        $this->client->jsonRequest('PATCH', '/api/admin/users/'.$target->getId(), [
            'name' => 'User Updated',
            'role' => 'ROLE_ADMIN',
        ], $this->authHeaders($token));

        self::assertResponseIsSuccessful();
        $updated = json_decode((string) $this->client->getResponse()->getContent(), true, 512, \JSON_THROW_ON_ERROR);
        \assert(\is_array($updated));
        self::assertSame('User Updated', $updated['name']);
        self::assertSame('ROLE_ADMIN', $updated['role']);
    }
}
