<?php

declare(strict_types=1);

namespace SamIT\abac\helpers;

use SamIT\abac\interfaces\Grant;

/**
 * Helper class for repositories that want to do some kind of caching on grant checks
 */
class Cache
{
    /**
     * @var array<string, bool>
     */
    private array $entries = [];

    public function set(Grant $grant, bool $allowed): void
    {
        $this->entries[$this->serializeGrant($grant)] = $allowed;
    }

    /**
     * @param Grant $grant
     * @return bool|null Whether the grant is allowed, null if we have no opinion
     */
    public function check(Grant $grant): ?bool
    {
        return $this->entries[$this->serializeGrant($grant)] ?? null;
    }

    private function serializeGrant(Grant $grant): string
    {
        $source = $grant->getSource();
        $target = $grant->getTarget();
        return "{$source->getAuthName()}|{$source->getId()}|{$target->getAuthName()}|{$target->getId()}|{$grant->getPermission()}";
    }
}
