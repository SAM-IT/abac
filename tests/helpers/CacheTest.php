<?php

declare(strict_types=1);

namespace test\helpers;

use PHPUnit\Framework\TestCase;
use SamIT\abac\helpers\Cache;
use SamIT\abac\values\Authorizable;
use SamIT\abac\values\Grant;

/**
 * @covers \SamIT\abac\helpers\Cache
 */
final class CacheTest extends TestCase
{
    public function testSetAndCheck(): void
    {
        $subject = new Cache();

        $grant = new Grant(new Authorizable('ab', 'cd'), new Authorizable('ef', 'gh'), 'ij');
        self::assertNull($subject->check($grant));

        $subject->set($grant, false);

        self::assertFalse($subject->check($grant));

        $subject->set($grant, true);

        self::assertTrue($subject->check($grant));
    }
}
