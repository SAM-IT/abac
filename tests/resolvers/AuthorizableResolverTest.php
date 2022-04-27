<?php

declare(strict_types=1);

namespace test\resolvers;

use SamIT\abac\interfaces\Resolver;
use SamIT\abac\resolvers\AuthorizableResolver;
use SamIT\abac\values\Authorizable;
use test\interfaces\ResolverTest;

/**
 * * @covers \SamIT\abac\resolvers\AuthorizableResolver
 */
class AuthorizableResolverTest extends ResolverTest
{
    protected function getResolver(): Resolver
    {
        return new AuthorizableResolver();
    }

    /**
     * @return iterable<array{0: object, 1: Authorizable|null}>
     */
    public function fromSubjectProvider(): iterable
    {
        for ($i = 0; $i < 30; $i++) {
            $authorizable = new Authorizable("id$i", 'name');
            $source = $i % 3 <= 1 ? $authorizable : new \stdClass();
            $target = $i % 3 <= 1 ? $authorizable : null;
            yield [$source, $target];
        }
    }

    /**
     * @return iterable<array{0: Authorizable, 1: Authorizable}>
     */
    public function toSubjectProvider(): iterable
    {
        for ($i = 0; $i < 30; $i++) {
            $authorizable = new Authorizable("id$i", 'name');
            yield [$authorizable, $authorizable];
        }
    }
}
