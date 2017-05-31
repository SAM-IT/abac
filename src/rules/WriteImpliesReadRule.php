<?php


namespace prime\auth\rules;

use SamIT\ABAC\interfaces\Authorizable;
use SamIT\ABAC\Manager;
use SamIT\ABAC\interfaces\Rule;

/**
 * Class WriteImpliesReadRule
 * @package prime\auth\rules
 * This rule allows a user to read something as long as they can write it.
 */
class WriteImpliesReadRule implements Rule
{


    /**
     * @inheritdoc
     * "you can ... if [description]"
     */
    public function getDescription()
    {
        return "you can [write] it.";
    }

    /**
     * @param Authorizable $source
     * @param \SamIT\ABAC\interfaces\Authorizable $target
     * @return boolean
     */
    public function execute(Authorizable $source, Authorizable $target, \ArrayAccess $environment, Manager $manager)
    {
        
        return $manager->isAllowed($source, $target, Manager::PERMISSION_WRITE);
    }

    /**
     * @return string[] An array of class names that this rule applies to.
     */
    public function getTargetTypes()
    {
        return [];
    }

    /**
     * @return string The name of the permission that this rule grants.
     */
    public function getPermissionName()
    {
        return Manager::PERMISSION_READ;
    }
}