<?php
declare(strict_types=1);

namespace SamIT\abac\resolvers;

use SamIT\abac\interfaces\Authorizable;
use SamIT\abac\interfaces\Resolver;

/**
 * Class AuthorizableResolver
 * Resolves authorizables, essentially this is an identity resolver.
 * @package SamIT\abac\resolvers
 */
class AuthorizableResolver implements Resolver
{

    /**
     * @inheritDoc
     */
    public function fromSubject(object $object): ?Authorizable
    {
        return $object instanceof Authorizable ? $object : null;
    }

    /**
     * @inheritDoc
     */
    public function toSubject(Authorizable $authorizable): ?object
    {
        return $authorizable;
    }
}
