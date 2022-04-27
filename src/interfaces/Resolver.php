<?php


namespace SamIT\abac\interfaces;

use SamIT\abac\exceptions\UnresolvableException;

/**
 * Implementing this inside your application allows you to authorize entities without changing the entities themselves.
 */
interface Resolver
{
    /**
     * @param object $object Any application entity, if it is already an Authorizable the object itself should be returned
     * @return Authorizable An authorizable for use in permission checking
     * @throws UnresolvableException when the object cannot be resolved
     */
    public function fromSubject(object $object): Authorizable;

    /**
     * @param Authorizable $authorizable Any authorizable
     * @return object The subject of the authorizable
     * @throws UnresolvableException when the authorizable cannot be resolved
     */
    public function toSubject(Authorizable $authorizable): object;
}
