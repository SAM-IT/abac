<?php

declare(strict_types=1);

namespace SamIT\abac\resolvers;

use SamIT\abac\exceptions\UnresolvableException;
use SamIT\abac\interfaces\Authorizable;
use SamIT\abac\interfaces\Resolver;

/**
 * Class AuthorizableResolver
 * Resolves Authorizables, essentially this is an identity resolver.
 */
class AuthorizableResolver implements Resolver
{
    public function fromSubject(object $object): Authorizable
    {
        if ($object instanceof Authorizable) {
            return $object;
        }
        throw UnresolvableException::forSubject($object);
    }

    public function toSubject(Authorizable $authorizable): object
    {
        return $authorizable;
    }
}
