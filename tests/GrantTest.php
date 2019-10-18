<?php
declare(strict_types=1);

namespace test;

class GrantTest extends \PHPUnit\Framework\TestCase
{

    /**
     * @covers \SamIT\abac\Grant
     */
    public function testGetters()
    {
        $source = new \SamIT\abac\Authorizable('a', 'b');
        $target = new \SamIT\abac\Authorizable( 'c', 'd');
        $permission = 'e';

        $grant = new \SamIT\abac\Grant($source, $target, $permission);
        $this->assertSame($source, $grant->getSource());
        $this->assertSame($target, $grant->getTarget());
        $this->assertSame($permission, $grant->getPermission());

    }

}