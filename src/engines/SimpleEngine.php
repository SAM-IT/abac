<?php

declare(strict_types=1);

namespace SamIT\abac\engines;

use SamIT\abac\interfaces\AccessChecker;
use SamIT\abac\interfaces\Environment;
use SamIT\abac\interfaces\RuleEngine;
use SamIT\abac\interfaces\SimpleRule;

class SimpleEngine implements RuleEngine
{
    /**
     * @var list<SimpleRule>
     */
    private readonly array $rules;

    public function __construct(SimpleRule ...$rules)
    {
        $this->rules = array_values($rules);
    }

    public function check(
        object $source,
        object $target,
        string $permission,
        Environment $environment,
        AccessChecker $accessChecker
    ): bool {
        foreach ($this->rules as $rule) {
            if ($rule->execute($source, $target, $permission, $environment, $accessChecker)) {
                return true;
            }
        }
        return false;
    }
}
