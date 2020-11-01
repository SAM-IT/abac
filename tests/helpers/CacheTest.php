<?php
declare(strict_types=1);

namespace test\helpers;

use PHPUnit\Framework\TestCase;
use SamIT\abac\helpers\Cache;
use SamIT\abac\values\Authorizable;
use SamIT\abac\values\Grant;

class CacheTest extends TestCase
{

    public function testSetAndCheck()
    {
        $subject = new Cache();

        $grant = new Grant(new Authorizable('ab', 'cd'), new Authorizable('ef', 'gh'), 'ij');
        $this->assertNull($subject->check($grant));

        $subject->set($grant, false);

        $this->assertFalse($subject->check($grant));

        $subject->set($grant, true);

        $this->assertTrue($subject->check($grant));
    }
}
