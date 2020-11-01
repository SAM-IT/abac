<?php


namespace SamIT\abac\engines;

use SamIT\abac\interfaces\AccessChecker;
use SamIT\abac\interfaces\Environment;
use SamIT\abac\interfaces\RuleEngine;
use SamIT\abac\interfaces\SimpleRule;

class SimpleEngine implements RuleEngine
{
    /** @var SimpleRule[] */
    private $rules = [];

    /**
     * SimpleEngine constructor.
     * @param SimpleRule[]|iterable $rules The rules this engine should use
     */
    public function __construct(iterable $rules)
    {
        foreach ($rules as $rule) {
            if (!$rule instanceof SimpleRule) {
                throw new \InvalidArgumentException('Rules must implement SimpleRule');
            }
            $this->rules[] = $rule;
        }
    }

    /**
     * @inheritDoc
     */
    public function check(
        object $source,
        object $target,
        string $permission,
        Environment $environment,
        AccessChecker $recursiveLookup
    ): bool {
        foreach ($this->rules as $rule) {
            if ($rule->execute($source, $target, $permission, $environment, $recursiveLookup)) {
                return true;
            }
        }
        return false;
    }
}
