<?php


namespace SamIT\abac\rules;


use SamIT\abac\interfaces\AccessChecker;
use SamIT\abac\interfaces\Authorizable;
use SamIT\abac\interfaces\Environment;
use SamIT\abac\interfaces\SimpleRule;

class AdminRole implements SimpleRule
{
    private $admins = [];

    /**
     * AdminRole constructor.
     * @param Authorizable[] $admins
     */
    public function __construct(array $admins)
    {
        foreach($admins as $admin) {
            $this->admins["{$admin->getAuthName()}|{$admin->getId()}"] = true;
        }
    }

    /**
     * @inheritdoc
     * @codeCoverageIgnore
     */
    public function getDescription(): string
    {
        return "you are an admin";
    }

    /**
     * @inheritdoc
     */
    public function execute(
        object $source,
        object $target,
        string $permission,
        Environment $environment,
        AccessChecker $manager
    ): bool
    {
        return isset($this->admins["{$source->getAuthName()}|{$source->getId()}"]);
    }
}