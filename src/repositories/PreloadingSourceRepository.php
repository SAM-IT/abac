<?php
declare(strict_types=1);

namespace SamIT\abac\repositories;

use SamIT\abac\helpers\Cache;
use SamIT\abac\interfaces\Authorizable;
use SamIT\abac\interfaces\Grant;
use SamIT\abac\interfaces\PermissionRepository;

/**
 * Repository that allows preloading permissions for a source.
 * Advantage of preloading is that we can serve cache misses too since we know we've loaded all explicit permissions.
 * @package SamIT\abac\repositories
 */
class PreloadingSourceRepository implements PermissionRepository
{
    private PermissionRepository $permissionRepository;

    private Cache $cache;

    private array $loadedSources = [];

    private function serializeAuthorizable(Authorizable $authorizable): string
    {
        return "{$authorizable->getAuthName()}|{$authorizable->getId()}";
    }

    public function __construct(PermissionRepository $permissionRepository)
    {
        $this->permissionRepository = $permissionRepository;
        $this->cache = new Cache();
    }

    public function grant(Grant $grant): void
    {
        $this->permissionRepository->grant($grant);
        $this->cache->set($grant, true);
    }

    public function revoke(Grant $grant): void
    {
        $this->permissionRepository->revoke($grant);
        $this->cache->set($grant, false);
    }

    private function checkCache(Grant $grant): ?bool
    {
        // If we don't have the source preloaded we do nothing
        if (!isset($this->loadedSources[$this->serializeAuthorizable($grant->getSource())])) {
            return null;
        }
        return $this->cache->check($grant) || false;
    }

    public function check(Grant $grant): bool
    {
        return $this->checkCache($grant) ?? $this->permissionRepository->check($grant);
    }

    public function preloadSource(Authorizable $source): void
    {
        $this->loadedSources[$this->serializeAuthorizable($source)] = true;
        foreach ($this->permissionRepository->search($source, null, null) as $grant) {
            $this->cache->set($grant, true);
        }
    }

    public function search(?Authorizable $source, ?Authorizable $target, ?string $permission): iterable
    {
        return $this->permissionRepository->search($source, $target, $permission);
    }
}
