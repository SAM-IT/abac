<?php
declare(strict_types=1);

namespace test\interfaces;


use PHPUnit\Framework\TestCase;
use SamIT\abac\interfaces\Authorizable;
use SamIT\abac\interfaces\Resolver;

abstract class ResolverTest extends TestCase
{

    abstract protected function getResolver():Resolver;

    abstract public function subjectProvider(): iterable;

    /**
     * @dataProvider subjectProvider
     */
    public function testFromSubject(?object $subject, ?Authorizable $authorizable)
    {
        if (!isset($subject)) {
            return;
        }

        if ($authorizable === null) {
            $this->assertNull($this->getResolver()->fromSubject($subject));
        } else {
            $actual = $this->getResolver()->fromSubject($subject);
            $this->assertNotNull($actual);
            $this->assertSame($authorizable->getAuthName(), $actual->getAuthName());
            $this->assertSame($authorizable->getId(), $actual->getId());
        }
    }

    /**
     * @dataProvider subjectProvider
     */
    public function testToSubject(?object $subject, ?Authorizable $authorizable)
    {
        if (!isset($authorizable)) {
            return;
        }

        if ($subject === null) {
            $this->assertNull($this->getResolver()->toSubject($authorizable));
        } else {
            $actual = $this->getResolver()->toSubject($authorizable);
            $this->assertNotNull($actual);
            $this->assertEquals($subject, $actual);
        }
    }
}