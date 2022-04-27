<?php

declare(strict_types=1);

namespace test\helpers;

use PHPUnit\Framework\TestCase;
use SamIT\abac\helpers\AuthorizableSerializer;
use SamIT\abac\values\Authorizable;

/**
 * @covers \SamIT\abac\helpers\AuthorizableSerializer
 */
final class AuthorizableSerializerTest extends TestCase
{
    /**
     * @return iterable<array{0: Authorizable, 1: string, 2:string}>
     */
    public function provider(): iterable
    {
        yield [new Authorizable('abc', 'def'), '|', 'def|abc'];
        yield [new Authorizable('abc', 'def'), ',', 'def,abc'];
    }

    /**
     * @dataProvider provider
     */
    public function testInvoke(Authorizable $authorizable, string $separator, string $expected): void
    {
        $serializer = new AuthorizableSerializer($separator);
        self::assertSame($expected, $serializer($authorizable));
    }

    /**
     * @dataProvider provider
     */
    public function testSerialize(Authorizable $authorizable, string $separator, string $expected): void
    {
        $serializer = new AuthorizableSerializer($separator);
        self::assertSame($expected, $serializer->serialize($authorizable));
    }
}
