<?php


namespace SamIT\abac\rules;

use SamIT\abac\interfaces\AccessChecker;
use SamIT\abac\interfaces\Authorizable;
use SamIT\abac\interfaces\Environment;
use SamIT\abac\Manager;
use SamIT\abac\interfaces\Rule;

/**
 * Class WriteImpliesReadRule
 * This rule allows a user to read something as long as they can write it.
 */
class WriteImpliesReadRule implements Rule
{


    /**
     * @inheritdoc
     * "you can ... if [description]"
     */
    public function getDescription(): string
    {
        return "you can [write] it.";
    }

    /**
     * @param Authorizable $source
     * @param \SamIT\abac\interfaces\Authorizable $target
     * @return boolean
     */
    public function execute(
        Authorizable $source,
        Authorizable $target,
        string $permission,
        Environment $environment,
        AccessChecker $accessChecker
    ): bool {
        return $accessChecker->check($source, $target, Manager::PERMISSION_WRITE);
    }

    /**
     * @return string[] An array of class names that this rule applies to.
     */
    public function getTargetNames(): array
    {
        return [];
    }

    /**
     * @return string The name of the permission that this rule grants.
     */
    public function getPermissions(): array
    {
        return [Manager::PERMISSION_READ];
    }

    /**
     * @inheritDoc
     */
    public function getSourceNames(): array
    {
        return [];
    }
}