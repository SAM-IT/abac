<?php
declare(strict_types=1);


namespace SamIT\abac\repositories;


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
    private $permissionRepository;

    public function __construct(PermissionRepository $permissionRepository)
    {
        $this->permissionRepository = $permissionRepository;
    }

    /**
     * @inheritDoc
     */
    public function grant(Grant $grant): void
    {
        $this->permissionRepository->grant($grant);
        $this->addToCache($grant, true);
    }


    /**
     * @inheritDoc
     */
    public function revoke(Grant $grant): void
    {
        $this->permissionRepository->revoke($grant);
        $this->addToCache($grant, false);
    }

    private $cache = [];

    private function serializeGrant(Grant $grant): string
    {
        $source = $grant->getSource();
        $target = $grant->getTarget();
        return "{$source->getAuthName()}|{$source->getId()}|{$target->getAuthName()}|{$target->getId()}|{$grant->getPermission()}";

    }

    private function addToCache(Grant $grant, bool $allowed): void
    {
        $this->cache[$this->serializeGrant($grant)] = $allowed;
    }

    private function checkFromCache(Grant $grant): ?bool
    {
        return $this->cache[$this->serializeGrant($grant)] ?? null;
    }

    /**
     * @inheritDoc
     */
    public function check(Grant $grant): bool
    {
        if (null === $result = $this->checkFromCache($grant)) {
            $this->addToCache($grant, $result = $this->permissionRepository->check($grant));

        }
        return $result;
    }

    /**
     * Searching itself is never cached, but all search results are cached.
     */
    public function search(?Authorizable $source, ?Authorizable $target, ?string $permission): iterable
    {
        foreach($this->permissionRepository->search($source, $target, $permission) as $key => $grant) {
            $this->addToCache($grant, true);
            yield $key => $grant;
        }
    }
}