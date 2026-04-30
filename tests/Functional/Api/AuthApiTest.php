<?php

declare(strict_types=1);

namespace App\Tests\Functional\Api;

final class AuthApiTest extends ApiWebTestCase
{
    public function testRegisterUser(): void
    {
        $interest = $this->createInterest('music');

        $this->client->jsonRequest('POST', '/api/auth/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'secret123',
            'interestIds' => [(int) $interest->getId()],
        ]);

        self::assertResponseStatusCodeSame(201);
        $data = json_decode((string) $this->client->getResponse()->getContent(), true, 512, \JSON_THROW_ON_ERROR);
        \assert(\is_array($data));
        self::assertSame('john@example.com', $data['email']);
    }

    public function testForgotPasswordReturnsSuccessMessage(): void
    {
        $this->createUser('john@example.com', 'secret123');

        $this->client->jsonRequest('POST', '/api/auth/forgot-password', [
            'email' => 'john@example.com',
        ]);

        self::assertResponseIsSuccessful();
        $data = json_decode((string) $this->client->getResponse()->getContent(), true, 512, \JSON_THROW_ON_ERROR);
        \assert(\is_array($data));
        self::assertArrayHasKey('message', $data);
    }

    public function testResetPasswordSuccess(): void
    {
        $this->createUser('john@example.com', 'old-password');
        $this->createPasswordReset('john@example.com', 'valid-token');

        $this->client->jsonRequest('POST', '/api/auth/reset-password', [
            'token' => 'valid-token',
            'newPassword' => 'new-password',
        ]);

        self::assertResponseIsSuccessful();
        $data = json_decode((string) $this->client->getResponse()->getContent(), true, 512, \JSON_THROW_ON_ERROR);
        \assert(\is_array($data));
        self::assertSame('Password updated successfully.', $data['message']);

        $token = $this->loginAndGetToken($this->client, 'john@example.com', 'new-password');
        self::assertNotSame('', $token);
    }

    public function testResetPasswordInvalidTokenReturns400(): void
    {
        $this->client->jsonRequest('POST', '/api/auth/reset-password', [
            'token' => 'invalid-token',
            'newPassword' => 'new-password',
        ]);

        self::assertResponseStatusCodeSame(400);
    }
}
