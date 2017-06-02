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
    public function getDescription(): string
    {
        return "the {target} is you.";
    }

    /**
     * @inheritdoc
     */
    public function execute(Authorizable $source, Authorizable $target, \ArrayAccess $environment, Manager $manager, string $permission): bool
    {
        return ($source instanceof User)
            && ($target instanceof User)
            && $source->getId() === $target->getId();
    }

    /**
     * @return string[] An array of class names that this rule applies to.
     */
    public function getTargetNames(): array
    {
        return [User::class];
    }

    /**
     * @return string The name of the permission that this rule grants.
     */
    public function getPermissions(): array
    {
        return [Manager::PERMISSION_READ];
    }
}