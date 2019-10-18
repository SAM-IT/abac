<?php
declare(strict_types=1);

namespace SamIT\abac\repositories;


use SamIT\abac\interfaces\Authorizable;
use SamIT\abac\interfaces\Grant;
use SamIT\abac\interfaces\PermissionRepository;

class MemoryRepository implements PermissionRepository
{
    /**
     * @var Grant[]
     */
    private $grants = [];

    /**
     * @inheritDoc
     */
    public function grant(Grant $grant): void
    {
        // Note that this stores a strong reference, when PHP 7.4 hits it makes sense to use a weak reference instead.
        $this->grants[spl_object_hash($grant)] = $grant;
    }

    /**
     * @inheritDoc
     */
    public function revoke(Grant $grant): void
    {
        unset($this->grants[spl_object_hash($grant)]);
    }

    /**
     * @inheritDoc
     */
    public function check(Grant $grant): bool
    {
        return isset($this->grants[spl_object_hash($grant)]);
    }

    /**
     * @inheritDoc
     */
    public function search(?Authorizable $source, ?Authorizable $target, ?string $permission): iterable
    {
        foreach($this->grants as $grant) {
            // Match permission
            if (isset($permission) && $grant->getPermission() !== $permission) {
                continue;
            }
            // Match source
            if (isset($source)
                && ($grant->getSource()->getAuthName() !== $source->getAuthName() || $grant->getSource()->getId() !== $source->getId())
            ) {
                continue;
            }

            // Match target
            if (isset($target)
                && ($grant->getTarget()->getAuthName() !== $target->getAuthName() || $grant->getTarget()->getId() !== $target->getId())
            ) {
                continue;
            }

            yield $grant;
        }
    }
}