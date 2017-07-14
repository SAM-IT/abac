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
        return $permission === 'dummy';
    }

    /**
     * Specifying this allows the manager to optimize rule evaluation by not executing a rule if it can never grant
     * anything for a target type.
     * @return string[] An array of class names that this rule applies to.
     */
    public function getTargetNames(): array
    {
        return [];
    }

    /**
     * Specifying this allows the manager to optimize rule evaluation by not executing a rule if it can never grant the
     * requested permission.
     * @return string The name of the permissions that this rule grants.
     */
    public function getPermissions(): array
    {
        return ['dummy'];
    }
}