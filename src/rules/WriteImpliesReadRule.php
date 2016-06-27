<?php


namespace prime\auth\rules;

use SamIT\ABAC\Authorizable;
use SamIT\ABAC\Manager;
use SamIT\ABAC\User;

class WriteImpliesReadRule implements Rule
{


    /**
     * @inheritdoc
     */
    public function getDescription()
    {
        return "you can [write] it.";
    }

    /**
     * @param Authorizable $source
     * @param Authorizable $target
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