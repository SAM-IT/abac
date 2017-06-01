<?php


namespace SamIT\abac\rules;


use SamIT\abac\interfaces\Authorizable;
use SamIT\abac\Manager;
use SamIT\abac\interfaces\Rule;


abstract class UserCanReadSelf implements Rule
{



    /**
     * @inheritdoc
     */
    public function getDescription()
    {
        return "the {target} is you.";
    }

    /**
     * @param \SamIT\abac\interfaces\Authorizable $source
     * @param \SamIT\abac\interfaces\Authorizable $target
     * @return boolean
     */
    public function execute(Authorizable $source, Authorizable $target, \ArrayAccess $environment, Manager $manager)
    {
        return ($source instanceof User)
            && ($target instanceof User)
            && $source->getId() === $target->getId();
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