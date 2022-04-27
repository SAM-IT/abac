<?php

declare(strict_types=1);

namespace test\interfaces;

use PHPUnit\Framework\TestCase;
use SamIT\abac\exceptions\UnresolvableException;
use SamIT\abac\interfaces\Authorizable;
use SamIT\abac\interfaces\Resolver;

abstract class ResolverTest extends TestCase
{
    abstract protected function getResolver(): Resolver;

    /**
     * @return iterable<array{0: object, 1: Authorizable|null }>
     */
    abstract public function fromSubjectProvider(): iterable;

    /**
     * @return iterable<array{0: object|null, 1: Authorizable }>
     */
    abstract public function toSubjectProvider(): iterable;

    /**
     * @dataProvider fromSubjectProvider
     */
    public function testFromSubject(object $subject, ?Authorizable $authorizable): void
    {
        if ($authorizable === null) {
            $this->expectException(UnresolvableException::class);
            $this->getResolver()->fromSubject($subject);
        } else {
            $actual = $this->getResolver()->fromSubject($subject);
            self::assertSame($authorizable->getAuthName(), $actual->getAuthName());
            self::assertSame($authorizable->getId(), $actual->getId());
        }
    }

    /**
     * @dataProvider toSubjectProvider
     */
    public function testToSubject(?object $subject, Authorizable $authorizable): void
    {
        if ($subject === null) {
            $this->expectException(UnresolvableException::class);
            $this->getResolver()->toSubject($authorizable);
        } else {
            $actual = $this->getResolver()->toSubject($authorizable);
            self::assertSame($subject, $actual);
        }
    }
}
