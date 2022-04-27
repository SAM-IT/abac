<?php

declare(strict_types=1);

namespace SamIT\abac\resolvers;

use SamIT\abac\exceptions\UnresolvableException;
use SamIT\abac\interfaces\Authorizable;
use SamIT\abac\interfaces\Resolver;

/**
 * Class ChainedResolver
 * Chain multiple resolvers
 */
class ChainedResolver implements Resolver
{
    /** @var list<Resolver> */
    private array $resolvers;
    public function __construct(Resolver ...$resolvers)
    {
        $this->resolvers = array_values($resolvers);
    }

    public function fromSubject(object $object): Authorizable
    {
        foreach ($this->resolvers as $resolver) {
            try {
                return $resolver->fromSubject($object);
            } catch (UnresolvableException) {
            }
        }
        throw UnresolvableException::forSubject($object);
    }

    public function toSubject(Authorizable $authorizable): object
    {
        foreach ($this->resolvers as $resolver) {
            try {
                return $resolver->toSubject($authorizable);
            } catch (UnresolvableException) {
            }
        }
        throw UnresolvableException::forSubject($authorizable);
    }
}
