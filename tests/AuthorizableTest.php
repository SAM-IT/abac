<?php

declare(strict_types=1);

namespace test;

/**
 * @covers \SamIT\abac\values\Authorizable
 */
class AuthorizableTest extends \PHPUnit\Framework\TestCase
{
    public function testGetters(): void
    {
        $a = new \SamIT\abac\values\Authorizable('0001', 'abc');
        self::assertSame('0001', $a->getId());
        self::assertSame('abc', $a->getAuthName());
    }
}
