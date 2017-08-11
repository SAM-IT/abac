<?php


namespace SamIT\abac\rules;

use SamIT\abac\interfaces\Authorizable;
use SamIT\abac\Manager;
use SamIT\abac\interfaces\Rule;

/**
 * Class ReadImpliesListRule
 * @package prime\auth\rules
 * This rule allows a user to list something as long as they can read it.
 */
class ReadImpliesListRule implements Rule
{


    /**
     * @inheritdoc
     * "you can ... if [description]"
     */
    public function getDescription(): string
    {
        return "you can [read] it.";
    }

    /**
     * @param Authorizable $source
     * @param \SamIT\abac\interfaces\Authorizable $target
     * @return boolean
     */
    public function execute(Authorizable $source, Authorizable $target, \ArrayAccess $environment, Manager $manager, string $permission): bool
    {
        
        return $manager->isAllowed($source, $target, Manager::PERMISSION_READ);
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
        return [Manager::PERMISSION_LIST];
    }
}