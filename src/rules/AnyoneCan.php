<?php


namespace SamIT\abac\rules;

use SamIT\abac\interfaces\AccessChecker;
use SamIT\abac\interfaces\Authorizable;
use SamIT\abac\interfaces\Environment;
use SamIT\abac\interfaces\SimpleRule;

/**
 */
class AnyoneCan implements SimpleRule
{
    private $permission;

    public function __construct(string $permission)
    {
        $this->permission = $permission;
    }

    /**
     * @inheritdoc
     * @codeCoverageIgnore
     */
    public function getDescription(): string
    {
        return "[permission] equals {$this->permission}";
    }

    /**
     * @return boolean
     */
    public function execute(
        object $source,
        object $target,
        string $permission,
        Environment $environment,
        AccessChecker $accessChecker
    ): bool {
        return $permission === $this->permission;
    }
}
