<?php

declare(strict_types=1);

namespace App\Tests\Smoke;

use PHPUnit\Framework\Attributes\IgnoreDeprecations;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[IgnoreDeprecations]
final class KernelBootTest extends KernelTestCase
{
    public function testKernelBoots(): void
    {
        self::bootKernel();
        self::assertTrue(self::getContainer()->has('service_container'));
    }
}
