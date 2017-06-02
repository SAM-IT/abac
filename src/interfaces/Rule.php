<?php


namespace SamIT\abac\interfaces;


use SamIT\abac\interfaces\Authorizable;
use SamIT\abac\Manager;

interface Rule
{
    /**
     * @return string[] The names of the permission that this rule grants. If empty, this rule applies to all permissions.
     */
    public function getPermissions(): array;

    /**
     * @return string[] An array of class names that this rule applies to. If empty, this rule applies to all target types.
     */
    public function getTargetNames(): array;
    
    /**
     * @return string A human readable description of what this rule does.
     * Finish the sentence: "You can [permission] the [object] if.."
     */
    public function getDescription(): string;
    /**
     * @param Authorizable $source
     * @param Authorizable $target
     * @param \ArrayAccess $environment The environment
     * @param Manager $manager The auth manager, use this for recursive lookups.
     * @param string $permission The requested permission
     */
    public function execute(Authorizable $source, Authorizable $target, \ArrayAccess $environment, Manager $manager, string $permission): bool;
}