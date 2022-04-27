<?php

declare(strict_types=1);

namespace test;

use SamIT\abac\values\Authorizable;
use SamIT\abac\values\Grant as Grant;

/**
 * @covers \SamIT\abac\values\Grant
 */
class GrantTest extends \PHPUnit\Framework\TestCase
{
    public function testGetters(): void
    {
        $source = new Authorizable('a', 'b');
        $target = new Authorizable('c', 'd');
        $permission = 'e';

        $grant = new Grant($source, $target, $permission);
        self::assertSame($source, $grant->getSource());
        self::assertSame($target, $grant->getTarget());
        self::assertSame($permission, $grant->getPermission());
    }
}
