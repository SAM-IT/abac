<?php


namespace SamIT\abac\interfaces;

/**
 * Interface RuleEngine
 * Implementations should (probably):
 * - Implement some way to load / define rules
 * - Implement an efficient method of deciding which rules to execute
 */
interface RuleEngine
{
    public function check(
        object $source,
        object $target,
        string $permission,
        Environment $environment,
        AccessChecker $accessChecker
    ): bool;
}
