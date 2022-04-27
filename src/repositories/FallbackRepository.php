<?php

declare(strict_types=1);

namespace SamIT\abac\repositories;

use SamIT\abac\interfaces\Authorizable;
use SamIT\abac\interfaces\Grant;
use SamIT\abac\interfaces\PermissionRepository;

/**
 * Class FallbackRepository
 * This class allows for multiple explicit permission sources.
 * Rules:
 * - Grants are done only on the primary
 * - Lookups fall back in case no result was found in the first.
 * - Revokes are done in both
 * - Search returns results from both
 * @package SamIT\abac\repositories
 *
 */
class FallbackRepository implements PermissionRepository
{
    /**
     * @var PermissionRepository
     */
    private $primary;
    /**
     * @var PermissionRepository
     */
    private $secondary;

    public function __construct(PermissionRepository $primary, PermissionRepository $secondary)
    {
        $this->primary = $primary;
        $this->secondary = $secondary;
    }

    /**
     * @inheritDoc
     */
    public function grant(Grant $grant): void
    {
        $this->primary->grant($grant);
    }

    /**
     * @inheritDoc
     */
    public function revoke(Grant $grant): void
    {
        $this->primary->revoke($grant);
        $this->secondary->revoke($grant);
    }

    /**
     * @inheritDoc
     */
    public function check(Grant $grant): bool
    {
        return $this->primary->check($grant) || $this->secondary->check($grant);
    }

    /**
     * @inheritDoc
     */
    public function search(?Authorizable $source, ?Authorizable $target, ?string $permission): iterable
    {
        yield from $this->primary->search($source, $target, $permission);
        yield from $this->secondary->search($source, $target, $permission);
    }
}
