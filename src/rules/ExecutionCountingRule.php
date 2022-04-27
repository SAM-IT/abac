<?php

declare(strict_types=1);

namespace SamIT\abac\rules;

use SamIT\abac\interfaces\AccessChecker;
use SamIT\abac\interfaces\Environment;
use SamIT\abac\interfaces\Rule;
use SamIT\abac\interfaces\SimpleRule;

final class ExecutionCountingRule implements Rule
{
    private int $counter = 0;

    public function __construct(private readonly SimpleRule $rule)
    {
    }

    public function getExecutions(): int
    {
        return $this->counter;
    }

    public function getDescription(): string
    {
        return $this->rule->getDescription();
    }

    public function execute(
        object $source,
        object $target,
        string $permission,
        Environment $environment,
        AccessChecker $accessChecker
    ): bool {
        $this->counter++;
        return $this->rule->execute($source, $target, $permission, $environment, $accessChecker);
    }

    public function getPermissions(): array
    {
        return $this->rule instanceof Rule ? $this->rule->getPermissions() : [];
    }

    public function getTargetNames(): array
    {
        return $this->rule instanceof Rule ? $this->rule->getTargetNames() : [];
    }

    public function getSourceNames(): array
    {
        return $this->rule instanceof Rule ? $this->rule->getSourceNames() : [];
    }
}
