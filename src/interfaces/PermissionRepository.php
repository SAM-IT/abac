<?php


namespace SamIT\abac\interfaces;


interface PermissionRepository
{
    /**
     * Store a permission in persistent storage
     * @param Grant $grant
     * @throws \RuntimeException if the Source does not have access to Target after execution
     */
    public function grant(Grant $grant): void;

    /**
     * Remove a permission from persistent storage
     * @param Grant $grant
     * @throws \RuntimeException if the Source still has access to Target after execution
     */
    public function revoke(Grant $grant): void;

    /**
     * @param Grant $grant
     * @return bool whether the Grant exists
     */
    public function check(Grant $grant): bool;

    /**
     * Search for grants using a query-by-example approach.
     * Skipping all arguments will return all grants in persistent storage.
     * @param ?Authorizable $source
     * @param ?Authorizable $target
     * @param ?string $permission
     * @return Grant[]
     */
    public function search(?Authorizable $source, ?Authorizable $target, ?string $permission): iterable;

}