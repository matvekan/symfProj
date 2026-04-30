<?php

declare(strict_types=1);

namespace App\Tests\Functional\Api;

final class InterestApiTest extends ApiWebTestCase
{
    public function testListInterestsRequiresAuthentication(): void
    {
        $this->client->request('GET', '/api/interests');

        self::assertResponseStatusCodeSame(401);
    }

    public function testListInterestsReturnsDataForAuthenticatedUser(): void
    {
        $this->createInterest('music');
        $this->createInterest('code');
        $this->createUser('john@example.com', 'secret123');

        $token = $this->loginAndGetToken($this->client, 'john@example.com', 'secret123');
        $this->client->request('GET', '/api/interests', server: $this->authHeaders($token));

        self::assertResponseIsSuccessful();
        $data = json_decode((string) $this->client->getResponse()->getContent(), true, 512, \JSON_THROW_ON_ERROR);
        \assert(\is_array($data));
        \assert(\is_array($data[0] ?? null));
        \assert(\is_array($data[1] ?? null));
        self::assertCount(2, $data);
        self::assertSame('code', $data[0]['name']);
        self::assertSame('music', $data[1]['name']);
    }
}
