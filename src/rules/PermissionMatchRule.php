<?php


namespace SamIT\abac\rules;


use SamIT\abac\interfaces\AccessChecker;
use SamIT\abac\interfaces\Environment;
use SamIT\abac\interfaces\SimpleRule;

class PermissionMatchRule implements SimpleRule
{
    /**
     * @var string
     */
    private $pattern;

    public function __construct(string $pattern)
    {
        $this->pattern = $pattern;
    }

    /**
     * @inheritDoc
     * @codeCoverageIgnore
     */
    public function getDescription(): string
    {
        return '[permission] matches ' . $this->pattern;
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
        return preg_match($this->pattern, $permission);
    }
}