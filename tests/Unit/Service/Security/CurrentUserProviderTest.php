<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Security;

use App\Entity\User;
use App\Service\Security\CurrentUserProvider;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\User\UserInterface;

#[AllowMockObjectsWithoutExpectations]
final class CurrentUserProviderTest extends TestCase
{
    public function testGetCurrentUserReturnsUserWhenAuthenticatedUserIsAppUser(): void
    {
        $user = new User();
        $security = $this->createMock(Security::class);
        $security->method('getUser')->willReturn($user);

        $provider = new CurrentUserProvider($security);

        self::assertSame($user, $provider->getCurrentUser());
    }

    public function testGetCurrentUserReturnsNullForNonAppUser(): void
    {
        $nonAppUser = $this->createMock(UserInterface::class);
        $security = $this->createMock(Security::class);
        $security->method('getUser')->willReturn($nonAppUser);

        $provider = new CurrentUserProvider($security);

        self::assertNull($provider->getCurrentUser());
    }
}
