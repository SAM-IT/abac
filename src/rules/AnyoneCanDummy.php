<?php


namespace SamIT\abac\rules;


use SamIT\abac\interfaces\Authorizable;
use SamIT\abac\Manager;
use SamIT\abac\interfaces\Rule;

/**
 * Class UserCanReadSelf
 * @package SamIT\abac\rules
 * Allow anyone to do dummy operations on anything.
 */
class AnyoneCanDummy implements Rule
{



    /**
     * @inheritdoc
     */
    public function getDescription(): string
    {
        return "true.";
    }

    /**
     * @param \SamIT\abac\interfaces\Authorizable $source
     * @param \SamIT\abac\interfaces\Authorizable $target
     * @return boolean
     */
    public function execute(Authorizable $source, Authorizable $target, \ArrayAccess $environment, Manager $manager, string $permission): bool
    {
        return true;
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
        return ['dummy'];
    }
}