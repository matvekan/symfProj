<?php

declare(strict_types=1);

namespace App\Tests\Functional\Api;

final class MeApiTest extends ApiWebTestCase
{
    public function testGetMeRequiresAuthentication(): void
    {
        $this->client->request('GET', '/api/me');

        self::assertResponseStatusCodeSame(401);
    }

    public function testGetMeReturnsCurrentUser(): void
    {
        $this->createUser('john@example.com', 'secret123', 'ROLE_USER', 'John');
        $token = $this->loginAndGetToken($this->client, 'john@example.com', 'secret123');

        $this->client->request('GET', '/api/me', server: $this->authHeaders($token));

        self::assertResponseIsSuccessful();
        $data = json_decode((string) $this->client->getResponse()->getContent(), true, 512, \JSON_THROW_ON_ERROR);
        \assert(\is_array($data));
        self::assertSame('john@example.com', $data['email']);
    }

    public function testPatchMeUpdatesName(): void
    {
        $this->createUser('john@example.com', 'secret123', 'ROLE_USER', 'John');
        $token = $this->loginAndGetToken($this->client, 'john@example.com', 'secret123');

        $this->client->jsonRequest('PATCH', '/api/me', ['name' => 'John Updated'], $this->authHeaders($token));

        self::assertResponseIsSuccessful();
        $data = json_decode((string) $this->client->getResponse()->getContent(), true, 512, \JSON_THROW_ON_ERROR);
        \assert(\is_array($data));
        self::assertSame('John Updated', $data['name']);
    }

    public function testPutMeInterestsReplacesInterests(): void
    {
        $first = $this->createInterest('music');
        $second = $this->createInterest('code');

        $user = $this->createUser('john@example.com', 'secret123', 'ROLE_USER', 'John');
        $user->addInterest($first);
        $this->em->flush();

        $token = $this->loginAndGetToken($this->client, 'john@example.com', 'secret123');
        $this->client->jsonRequest('PUT', '/api/me/interests', [
            'interestIds' => [(int) $second->getId()],
        ], $this->authHeaders($token));

        self::assertResponseIsSuccessful();
        $data = json_decode((string) $this->client->getResponse()->getContent(), true, 512, \JSON_THROW_ON_ERROR);
        \assert(\is_array($data));
        \assert(isset($data['interests']) && \is_array($data['interests']));
        \assert(\is_array($data['interests'][0] ?? null));
        self::assertCount(1, $data['interests']);
        self::assertSame('code', $data['interests'][0]['name']);
    }
}
