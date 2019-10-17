<?php


namespace SamIT\abac\interfaces;

/**
 * Interface RuleEngine
 * @package SamIT\abac\interfaces
 * Implementations should (probably):
 * - Implement some way to load / define rules
 * - Implement an efficient method of deciding which rules to execute
 */
interface RuleEngine
{

    /**
     * @param Authorizable $source
     * @param Authorizable $target
     * @param string $permission
     * @param Environment $environment
     * @param AccessChecker $accessChecker
     * @return bool
     */
    public function check(
        Authorizable $source,
        Authorizable $target,
        string $permission,
        Environment $environment,
        AccessChecker $accessChecker
    ): bool;

}