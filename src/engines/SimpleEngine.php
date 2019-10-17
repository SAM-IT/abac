<?php


namespace SamIT\abac\engines;


use SamIT\abac\interfaces\AccessChecker;
use SamIT\abac\interfaces\Authorizable;
use SamIT\abac\interfaces\Environment;
use SamIT\abac\interfaces\Rule;
use SamIT\abac\interfaces\RuleEngine;

class SimpleEngine implements RuleEngine
{
    /** @var Rule[] */
    private $rules = [];

    /**
     * SimpleEngine constructor.
     * @param Rule[]|iterable $rules The rules this engine should use
     */
    public function __construct(iterable $rules)
    {
        foreach($rules as $rule) {
            if (!$rule instanceof Rule) {
                throw new \InvalidArgumentException('Rules must implement Rule');
            }
            $this->rules[] = $rule;
        }
    }

    /**
     * @inheritDoc
     */
    public function check(
        Authorizable $source,
        Authorizable $target,
        string $permission,
        Environment $environment,
        AccessChecker $recursiveLookup
    ): bool {
        foreach($this->rules as $rule) {
            if ($rule->execute($source, $target, $permission, $environment, $recursiveLookup)) {
                return true;
            }
        }
        return false;
    }
}