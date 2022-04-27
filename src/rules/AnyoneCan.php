<?php

declare(strict_types=1);

namespace SamIT\abac\rules;

use SamIT\abac\interfaces\AccessChecker;
use SamIT\abac\interfaces\Environment;
use SamIT\abac\interfaces\Rule;
use SamIT\abac\interfaces\SimpleRule;

/**
 */
class AnyoneCan implements Rule
{
    public function __construct(private readonly string $permission)
    {
    }

    public function getDescription(): string
    {
        return "[permission] equals {$this->permission}";
    }

    public function execute(
        object $source,
        object $target,
        string $permission,
        Environment $environment,
        AccessChecker $accessChecker
    ): bool {
        return $permission === $this->permission;
    }

    public function getPermissions(): array
    {
        return [$this->permission];
    }

    public function getTargetNames(): array
    {
        return [];
    }

    public function getSourceNames(): array
    {
        return [];
    }
}
