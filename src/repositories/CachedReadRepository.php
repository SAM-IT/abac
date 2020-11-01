<?php
declare(strict_types=1);


namespace SamIT\abac\repositories;

use SamIT\abac\helpers\Cache;
use SamIT\abac\interfaces\Authorizable;
use SamIT\abac\interfaces\Grant;
use SamIT\abac\interfaces\PermissionRepository;

/**
 * Class CachedReadRepository
 * Caches results from a repository in memory for the duration of a request
 * @package SamIT\abac
 */
class CachedReadRepository implements PermissionRepository
{
    /**
     * @var PermissionRepository
     */
    private PermissionRepository $permissionRepository;

    private Cache $cache;

    public function __construct(PermissionRepository $permissionRepository)
    {
        $this->permissionRepository = $permissionRepository;
        $this->cache = new Cache();
    }

    /**
     * @inheritDoc
     */
    public function grant(Grant $grant): void
    {
        $this->permissionRepository->grant($grant);
        $this->cache->set($grant, true);
    }


    /**
     * @inheritDoc
     */
    public function revoke(Grant $grant): void
    {
        $this->permissionRepository->revoke($grant);
        $this->cache->set($grant, false);
    }


    /**
     * @inheritDoc
     */
    public function check(Grant $grant): bool
    {
        if (null === $result = $this->cache->check($grant)) {
            $this->cache->set($grant, $result = $this->permissionRepository->check($grant));
        }
        return $result;
    }

    /**
     * Searching itself is never cached, but all search results are cached.
     */
    public function search(?Authorizable $source, ?Authorizable $target, ?string $permission): iterable
    {
        foreach ($this->permissionRepository->search($source, $target, $permission) as $key => $grant) {
            $this->cache->set($grant, true);
            yield $key => $grant;
        }
    }
}
