<?php


namespace prime\auth\rules;

use SamIT\ABAC\Authorizable;
use SamIT\ABAC\Manager;
use SamIT\ABAC\User;

class UserWriteImpliesReadRule implements Rule
{


    /**
     * @return string A human readable description of what this rule does.
     */
    public function getDescription()
    {
        return "can write the {target}.";
    }

    /**
     * @param Authorizable $source
     * @param Authorizable $target
     * @return boolean
     */
    public function execute(Authorizable $source, Authorizable $target, \ArrayAccess $environment, Manager $manager)
    {
        
        return ($source instanceof User)
        && ($target instanceof User)
        && $manager->isAllowed($source, $target, Manager::PERMISSION_WRITE);
    }

    /**
     * @return string[] An array of class names that this rule applies to.
     */
    public function getTargetTypes()
    {
        return [User::class];
    }

    /**
     * @return string The name of the permission that this rule grants.
     */
    public function getPermissionName()
    {
        return Manager::PERMISSION_READ;
    }
}