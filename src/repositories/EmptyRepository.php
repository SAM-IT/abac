<?php
declare(strict_types=1);

namespace SamIT\abac\repositories;

use SamIT\abac\interfaces\Authorizable;
use SamIT\abac\interfaces\Grant;
use SamIT\abac\interfaces\PermissionRepository;

class EmptyRepository implements PermissionRepository
{

    /**
     * @inheritDoc
     */
    public function grant(Grant $grant): void
    {
        throw new \RuntimeException('Granting not supported');
    }

    /**
     * @inheritDoc
     */
    public function revoke(Grant $grant): void
    {
        return;
    }

    /**
     * @inheritDoc
     */
    public function check(Grant $grant): bool
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function search(?Authorizable $source, ?Authorizable $target, ?string $permission): iterable
    {
        return [];
    }
}
