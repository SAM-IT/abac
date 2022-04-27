<?php

declare(strict_types=1);

namespace SamIT\abac\interfaces;

/**
 * Interface SimpleRule
 * This interface is the minimum that must be implemented by a Rule Class.
 * Because of its simplicity little optimization is possible by the rule engine.
 */
interface SimpleRule
{
    /**
     * @return non-empty-string A human readable description of what this rule does.
     * Finish the sentence: "You can [permission] the [object] if.."
     */
    public function getDescription(): string;

    /**
     * @param object $source
     * @param object $target
     * @param string $permission
     * @param Environment $environment
     * @param AccessChecker $accessChecker
     * @return bool
     */
    public function execute(
        object $source,
        object $target,
        string $permission,
        Environment $environment,
        AccessChecker $accessChecker
    ): bool;
}
