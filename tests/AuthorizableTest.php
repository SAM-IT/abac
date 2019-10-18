<?php
declare(strict_types=1);

namespace test;

class AuthorizableTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @covers \SamIT\abac\Authorizable
     */
    public function testGetters()
    {
        $a = new \SamIT\abac\Authorizable('0001', 'abc');
        $this->assertSame('0001', $a->getId());
        $this->assertSame('abc', $a->getAuthName());
    }

}
