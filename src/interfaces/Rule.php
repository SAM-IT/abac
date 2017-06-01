<?php


namespace SamIT\abac\interfaces;


use SamIT\abac\interfaces\Authorizable;
use SamIT\abac\Manager;

interface Rule
{
    /**
     * @return string The name of the permission that this rule grants.
     */
    public function getPermissionName();

    /**
     * @return string[] An array of class names that this rule applies to. 
     */
    public function getTargetTypes();
    
    /**
     * @return string A human readable description of what this rule does.
     * Finish the sentence: "You can [permission] the [object] if.."
     */
    public function getDescription();
    /**
     * @param Authorizable $source
     * @param Authorizable $target
     * @return boolean
     */
    public function execute(Authorizable $source, Authorizable $target, \ArrayAccess $environment, Manager $manager);
}