<?php


namespace SamIT\abac\interfaces;

use SamIT\abac\interfaces\Authorizable;
use SamIT\abac\Manager;

/**
 * Interface Rule
 * Rules implementing this interface can be optimized by a RuleEngine; for example if the requested permission is not in
 * the list returned by `getPermissions()` then the RuleEngine MAY skip this rule.
 * The rule itself MUST always check that the source, target and permission are within expected ranges.
 * @package SamIT\abac\interfaces
 */
interface Rule extends SimpleRule
{
    /**
     * The return value of this function should not change during the lifetime of an instance
     * @return string[] The names of the permission that this rule grants. If empty, this rule applies to all permissions.
     */
    public function getPermissions(): array;

    /**
     * The return value of this function should not change during the lifetime of an instance
     * @return string[] An array of source names that this rule applies to. If empty, this rule applies to all target types.
     */
    public function getTargetNames(): array;

    /**
     * The return value of this function should not change during the lifetime of an instance
     * @return string[] An array of target names that this rule applies to. If empty, this rule applies to all target types.
     */
    public function getSourceNames(): array;
}
