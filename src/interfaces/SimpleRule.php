<?php


namespace SamIT\abac\interfaces;


use SamIT\abac\interfaces\Authorizable;
use SamIT\abac\Manager;

/**
 * Interface SimpleRule
 * This interface is the minimum that must be implemented by a Rule Class.
 * Because of its simplicity little optimization is possible by the rule engine.
 * @package SamIT\abac\interfaces
 */
interface SimpleRule
{
    /**
     * @return string A human readable description of what this rule does.
     * Finish the sentence: "You can [permission] the [object] if.."
     */
    public function getDescription(): string;

    /**
     * @param Authorizable $source
     * @param Authorizable $target
     * @param string $permission
     * @param Environment $environment
     * @param AccessChecker $accessChecker
     * @return bool
     */
    public function execute(
        Authorizable $source,
        Authorizable $target,
        string $permission,
        Environment $environment,
        AccessChecker $accessChecker
    ): bool;
}