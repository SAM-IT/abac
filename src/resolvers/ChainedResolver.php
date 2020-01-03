<?php
declare(strict_types=1);

namespace SamIT\abac\resolvers;


use SamIT\abac\interfaces\Authorizable;
use SamIT\abac\interfaces\Resolver;

/**
 * Class ChainedResolver
 * Chain multiple resolvers
 * @package SamIT\abac\resolvers
 */
class ChainedResolver implements Resolver
{
    /** @var Resolver */
    private $resolvers;
    public function __construct(Resolver ...$resolvers)
    {
        $this->resolvers = $resolvers;
    }

    /**
     * @inheritDoc
     */
    public function fromSubject(object $object): ?Authorizable
    {
        foreach($this->resolvers as $resolver) {
            if (null !== $authorizable = $resolver->fromSubject($object)) {
                return $authorizable;
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function toSubject(Authorizable $authorizable): ?object
    {
        foreach($this->resolvers as $resolver) {
            if (null !== $subject = $resolver->toSubject($authorizable)) {
                return $subject;
            }
        }
    }
}