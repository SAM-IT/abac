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


    private function hashGrant(Grant $grant): string
    {
        return implode('.', [
            $grant->getPermission(),
            $grant->getSource()->getAuthName(),
            $grant->getSource()->getId(),
            $grant->getTarget()->getAuthName(),
            $grant->getTarget()->getId()
        ]);
    }

    /**
     * @inheritDoc
     */
    public function grant(Grant $grant): void
    {
        $this->grants[$this->hashGrant($grant)] = $grant;
    }

    /**
     * @inheritDoc
     */
    public function revoke(Grant $grant): void
    {
        unset($this->grants[$this->hashGrant($grant)]);
    }

    /**
     * @inheritDoc
     */
    public function check(Grant $grant): bool
    {
        return isset($this->grants[$this->hashGrant($grant)]);
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