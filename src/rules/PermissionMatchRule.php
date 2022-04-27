<?php

declare(strict_types=1);

namespace SamIT\abac\rules;

use SamIT\abac\interfaces\AccessChecker;
use SamIT\abac\interfaces\Environment;
use SamIT\abac\interfaces\Rule;

class PermissionMatchRule implements Rule
{
    /**
     * @param string $pattern
     * @param list<string> $sourceNames
     * @param list<string> $targetNames
     */
    public function __construct(
        private readonly string $pattern,
        private readonly array $sourceNames = [],
        private readonly array $targetNames = [],
    ) {
    }

    public function getDescription(): string
    {
        return '[permission] matches ' . $this->pattern;
    }

    public function execute(
        object $source,
        object $target,
        string $permission,
        Environment $environment,
        AccessChecker $accessChecker
    ): bool {
        return preg_match($this->pattern, $permission) === 1;
    }

    /**
     * @return list<string>
     */
    public function getPermissions(): array
    {
        return [];
    }

    /**
     * @return list<string>
     */
    public function getTargetNames(): array
    {
        return $this->targetNames;
    }

    /**
     * @return list<string>
     */
    public function getSourceNames(): array
    {
        return $this->sourceNames;
    }
}
