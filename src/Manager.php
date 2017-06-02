<?php

namespace SamIT\abac;

use SamIT\abac\interfaces\Authorizable;
use SamIT\abac\interfaces\Rule;

/**
 * Class Permission
 * @package app\models
 * @property string $permission
 * @property string $source
 * @property int $source_id
 * @property string $target
 * @property int $target_id
 */
abstract class Manager
{
    /**
     * @var
     */
    private $ruleMap = [];
    /**
     * @var array
     */
    private $permissionMap = [];

    const MAX_RECURSE = 10;
    const PERMISSION_READ = 'read';
    const PERMISSION_WRITE = 'write';
    const PERMISSION_SHARE = 'share';
    const PERMISSION_ADMIN = 'admin';
    const PERMISSION_INSTANTIATE = 'instantiate';

    /**
     * Admins bypass the
     * @var array
     */
    public $admins = [];

    /**
     * Checks if a permission is known.
     * @param string $permission
     * @throws \Exception
     */
    private function checkPermissionExists(string $permission) {
        return true;
        if (!array_key_exists($permission, $this->permissionMap)) {
            throw new \Exception("Unknown permission: $permission");
        }
    }

    public function grant(Authorizable $source, Authorizable $target, string $permission)
    {
        $this->checkPermissionExists($permission);
        $this->grantInternal($source->getAuthName(), $source->getId(), $target->getAuthName(), $target->getId(), $permission);
    }

    public function grantById(string $sourceName, string $sourceId, string $targetName, string $targetId, string $permission)
    {
        $this->checkPermissionExists($permission);

        if (!is_subclass_of($sourceName, Authorizable::class)) {
            throw new \Exception("Cannot grant access for unknown class: " . $sourceName);
        }

        if (!is_subclass_of($targetName, Authorizable::class)) {
            throw new \Exception("Cannot grant access to unknown class: " . $targetName);
        }

        $this->grantInternal($sourceName, $sourceId, $targetName, $targetId, $permission);

    }

    public function revoke(Authorizable $source, Authorizable $target, string $permission)
    {
        $this->checkPermissionExists($permission);
        $this->revokeInternal($source->getAuthName(), $source->getId(), $target->getAuthName(), $target->getId(), $permission);
    }

    public function revokeById(string $sourceName, string $sourceId, string $targetName, string $targetId, string $permission)
    {
        $this->checkPermissionExists($permission);

        if (!is_subclass_of($sourceName, Authorizable::class)) {
            throw new \Exception("Cannot grant access for unknown class: " . $sourceName);
        }

        if (!is_subclass_of($targetName, Authorizable::class)) {
            throw new \Exception("Cannot grant access to unknown class: " . $targetName);
        }

        $this->revokeInternal($sourceName, $sourceId, $targetName, $targetId, $permission);
    }

    /**
     * @param string $directory
     * @param string $namespace
     * @return int The number of rules imported.
     */
    public function importRules(string $directory, string $namespace)
    {
        $result = 0;
        // Scan directory.
        foreach(scandir($directory) as $file) {
            if (substr_compare($file, '.php', -4, 4, false) === 0) {
                $rc = new \ReflectionClass($namespace . "\\" . substr($file, 0, -4));
                if (!$rc->isAbstract()
                    && $rc->implementsInterface(Rule::class)
                    && ((null === $c = $rc->getConstructor()) || $c->getNumberOfRequiredParameters() === 0)
                ) {
                    $this->addRule($rc->newInstance());
                    $result++;
                }
            }
        }

        return $result;
    }
    /**
     * Adds the included rules
     */
    protected function addCoreRules()
    {
        return $this->importRules(__DIR__ . '/rules', 'SamIT\\abac\\rules');
    }

    /**
     * Registers a rule with the system.
     * @param Rule $rule
     */
    public function addRule(Rule $rule)
    {
        if (!empty($rule->getTargetNames())) {
            foreach ($rule->getTargetNames() as $targetName) {
                if (!empty($rule->getPermissions())) {
                    foreach ($rule->getPermissions() as $permission) {
                        $key = "$permission|$targetName";
                        if (isset($this->ruleMap[$key])) {
                            $this->ruleMap[$key][] = $rule;
                        } else {
                            $this->ruleMap[$key] = [$rule];
                        }
                    }
                } else {
                    $key = "|$targetName";
                    if (isset($this->ruleMap[$key])) {
                        $this->ruleMap[$key][] = $rule;
                    } else {
                        $this->ruleMap[$key] = [$rule];
                    }
                }



            }
        } else {
            if (!empty($rule->getPermissions())) {
                foreach ($rule->getPermissions() as $permission) {
                    $key = "$permission|";
                    if (isset($this->ruleMap[$key])) {
                        $this->ruleMap[$key][] = $rule;
                    } else {
                        $this->ruleMap[$key] = [$rule];
                    }
                }
            } else {
                $key = "|";
                if (isset($this->ruleMap[$key])) {
                    $this->ruleMap[$key][] = $rule;
                } else {
                    $this->ruleMap[$key] = [$rule];
                }
            }
        }
//        if (isset($this->permissionMap[$rule->getPermissionName()])) {
//            $this->permissionMap[$rule->getPermissionName()][] = $rule;
//        } else {
//            $this->permissionMap[$rule->getPermissionName()] = [$rule];
//        }
    }

    public function __construct()
    {
        $this->addCoreRules();
    }

    private $depth = 0;

    /**
     * @param Authorizable $source The source for authorization, probably the user, group or role.
     * @param Authorizable $target Any object on which an operation is performed.
     * @param string $permission The name of the requested permission
     * @return bool whether the source should be allowed to perform the operation.
     * @throws \RuntimeException if the recursion passes a threshold
     */
    public function isAllowed(Authorizable $source, Authorizable $target, string $permission)
    {
        $this->depth++;
        if ($this->depth > self::MAX_RECURSE) {
            throw new \RuntimeException("Recursion too deep.");
        } try {
            return $this->isAllowedInternal($source, $target, $permission);
        } finally {
            $this->depth--;
        }
    }

    /**
     * @param Authorizable $source Source
     * @param Authorizable[] $targets List of targets
     * @param string $permission Permission
     * @return Authorizable[] The elements in $targets for which $source is allowed $permission.
     */
    public function filter(Authorizable $source, array $targets, string $permission)
    {
        return array_filter($targets, function(Authorizable $target) use ($source, $permission) {
            return $this->isAllowed($source, $target, $permission);
        });
    }

    /**
     * @param Authorizable $source
     * @param Authorizable $target
     * @param string $permission
     * @return bool whether the $source is allowed the $permission with respect to $target.
     */
    private function isAllowedInternal(Authorizable $source, Authorizable $target, string $permission)
    {
        try {
            $this->checkPermissionExists($permission);
            if ($this->isAllowedExplicit($source->getAuthName(), $source->getId(), $target->getAuthName(),
                $target->getId(), $permission)) {
                return true;
            }
            // Check specific rules
            $key = "$permission|{$target->getAuthName()}";
            if (isset($this->ruleMap[$key])) {
                /** @var Rule $rule */
                foreach ($this->ruleMap[$key] as $rule) {
                    if ($rule->execute($source, $target, $this->getEnvironment(), $this, $permission)) {
                        return true;
                    }
                }
            }

            // Check rules with no target.
            $key = "$permission|";
            if (isset($this->ruleMap[$key])) {
                /** @var Rule $rule */
                foreach ($this->ruleMap[$key] as $rule) {
                    if ($rule->execute($source, $target, $this->getEnvironment(), $this, $permission)) {
                        return true;
                    }
                }
            }

            // Check rules with no permission.
            $key = "|{$target->getAuthName()}";
            if (isset($this->ruleMap[$key])) {
                /** @var Rule $rule */
                foreach ($this->ruleMap[$key] as $rule) {
                    if ($rule->execute($source, $target, $this->getEnvironment(), $this, $permission)) {
                        return true;
                    }
                }
            }

            // Check generic rules.
            if ($key !== "|") {
                $key = "|";
                if (isset($this->ruleMap[$key])) {
                    /** @var Rule $rule */
                    foreach ($this->ruleMap[$key] as $rule) {
                        if ($rule->execute($source, $target, $this->getEnvironment(), $this, $permission)) {
                            return true;
                        }
                    }
                }
            }
        } finally {
//            var_dump($key, $rule); die();
        }
        return false;

    }

    /**
     * Create textual explanation of the rules.
     * @return string
     */
    public function getExplanation()
    {
        $result = [];
        /**
         * @var  $key
         * @var Rule $rule
         */
        foreach($this->ruleMap as $key => $rules) {
            list($permission, $target) = explode('|', $key);
            foreach($rules as $rule) {
                if (!empty($target)) {
                    $result[] = strtr("You can [$permission] a [$target] if " . $rule->getDescription(), [
                      "{target}" => "[$target]"

                    ]);
                } else {
                    $result[] = "You can [$permission] it if " . $rule->getDescription();
                }
            }
        }
        return implode("\n", $result);
    }

    /**
     * This function must persist the grant to storage
     */
    abstract protected function grantInternal(string $sourceName, string $sourceId, string $targetName, string $targetId, string $permission): void;

    /**
     * This function must remove a persisted grant from storage
     */
    abstract protected function revokeInternal(string $sourceName, string $sourceId, string $targetName, string $targetId, string $permission): void;

    /**
     * @return bool whether this grant exists in storage
     */
    abstract protected function isAllowedExplicit(string $sourceName, string $sourceId, string $targetName, string $targetId, string $permission): bool;

    /**
     * In attribute-based access control the rules can use the environment.
     * Anything needed by your rules should be passed in via this environment, rules themselves should not use
     * global variables, service locators or any other method to obtain information about the environment.
     * @return \ArrayObject
     */
    abstract protected function getEnvironment();



}