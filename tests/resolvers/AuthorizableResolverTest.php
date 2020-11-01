<?php
declare(strict_types=1);

namespace test;

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


    public function subjectProvider(): iterable
    {
        for ($i = 0; $i < 100; $i++) {
            $authorizable = new Authorizable("id$i", self::class);
            yield [$authorizable, $authorizable];
        }
    }
}
