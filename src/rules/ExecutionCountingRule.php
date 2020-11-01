<?php
declare(strict_types=1);

namespace SamIT\abac\rules;

use SamIT\abac\interfaces\AccessChecker;
use SamIT\abac\interfaces\Environment;
use SamIT\abac\interfaces\SimpleRule;

class ExecutionCountingRule implements SimpleRule
{
    /**
     * @var SimpleRule
     */
    private $rule;

    private $counter = 0;

    public function __construct(SimpleRule $rule)
    {
        $this->rule = $rule;
    }

    public function getExecutions(): int
    {
        return $this->counter;
    }

    /**
     * @inheritDoc
     * @codeCoverageIgnore
     */
    public function getDescription(): string
    {
        return '';
    }

    /**
     * @inheritDoc
     */
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
}
