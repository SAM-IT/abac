<?php
declare(strict_types=1);

namespace test;

class GrantTest extends \PHPUnit\Framework\TestCase
{

    /**
     * @covers \SamIT\abac\values\Grant
     */
    public function testGetters()
    {
        $source = new \SamIT\abac\values\Authorizable('a', 'b');
        $target = new \SamIT\abac\values\Authorizable('c', 'd');
        $permission = 'e';

        $grant = new \SamIT\abac\values\Grant($source, $target, $permission);
        $this->assertSame($source, $grant->getSource());
        $this->assertSame($target, $grant->getTarget());
        $this->assertSame($permission, $grant->getPermission());
    }
}
