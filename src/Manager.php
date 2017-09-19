<?php
declare(strict_types=1);

namespace SamIT\abac;

use function iter\rewindable\filter;
use SamIT\abac\interfaces\Authorizable;
use SamIT\abac\interfaces\Permission;
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

    const MAX_RECURSE = 10;

    /**
     * PERMISSIONS
     * Below is a list of constant identifying common used permissions.
     * You are free to use any string as a named permission, or alternatively
     * define constants within your own application, or in a subclass.
     */

    /**
     * List an entity; this means getting its ID and some textual identifier / display name.
     */
    const PERMISSION_LIST = 'list';
    /**
     * Read an entity, this includes all fields readable by any user and can contain privileged information.
     */
    const PERMISSION_READ = 'read';

    /**
     * Write an entity, this includes all fields writable by any user.
     * Often used with a rule that allows delete permission to anyone with write permission.
     * Often used with a rule that allows read permission to anyone with write permission.
     */
    const PERMISSION_WRITE = 'write';

    /**
     * Delete an entity.
     */
    const PERMISSION_DELETE = 'delete';

    /**
     * Allows someone to administer an entity.
     * Often used with a rule that allows admins to do anything.
     */
    const PERMISSION_ADMIN = 'admin';

    /**
     * Allows someone create an entity.
     */
    const PERMISSION_CREATE = 'create';

    /**
     * Allows someone to revoke permissions on an entity
     */
    const PERMISSION_REVOKE = 'revoke';

    /**
     * Allows someone to grant permissions on an entity.
     * Often combined with a check of the permission to be granted, ie you can't grant what you can't do.
     */
    const PERMISSION_GRANT = 'grant';

    /**
     * Admins bypass the
     * @var array
     */
    public $admins = [];

    public function grant(Authorizable $source, Authorizable $target, string $permission)
    {
        $this->grantInternal($source->getAuthName(), $source->getId(), $target->getAuthName(), $target->getId(), $permission);
    }

    public function grantById(string $sourceName, string $sourceId, string $targetName, string $targetId, string $permission)
    {
        $this->grantInternal($sourceName, $sourceId, $targetName, $targetId, $permission);
    }

    public function revoke(Authorizable $source, Authorizable $target, string $permission)
    {
        $this->revokeInternal($source->getAuthName(), $source->getId(), $target->getAuthName(), $target->getId(), $permission);
    }

    public function revokeById(string $sourceName, string $sourceId, string $targetName, string $targetId, string $permission)
    {
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

    protected $depth = 0;

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
    public function filter(?Authorizable $source, iterable $targets, string $permission): \Iterator
    {
        if (!isset($source)) {
            return new \EmptyIterator();
        }

        return filter(function($target) use ($source, $permission) {
            return $this->isAllowed($source, $target, $permission);
        }, $targets);
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
            if ($this->isAllowedExplicit($source->getAuthName(), $source->getId(), $target->getAuthName(),
                $target->getId(), $permission)) {
                return true;
            }
            // Check specific rules
            $key = "$permission|{$target->getAuthName()}";
            if (isset($this->ruleMap[$key])) {
                /** @var Rule $rule */
                foreach ($this->ruleMap[$key] as $rule) {
                    if ($this->execute($rule, $source, $target, $permission)) {
                        return true;
                    }
                }
            }

            // Check rules with no target.
            $key = "$permission|";
            if (isset($this->ruleMap[$key])) {
                /** @var Rule $rule */
                foreach ($this->ruleMap[$key] as $rule) {
                    if ($this->execute($rule, $source, $target, $permission)) {
                        return true;
                    }
                }
            }

            // Check rules with no permission.
            $key = "|{$target->getAuthName()}";
            if (isset($this->ruleMap[$key])) {
                /** @var Rule $rule */
                foreach ($this->ruleMap[$key] as $rule) {
                    if ($this->execute($rule, $source, $target, $permission)) {
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
                        if ($this->execute($rule, $source, $target, $permission)) {
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

    protected function execute(Rule $rule, Authorizable $source, Authorizable $target, string $permission): bool
    {
        return $rule->execute($source, $target, $this->getEnvironment(), $this, $permission);
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

                    $result[] = strtr("You can {permission} a {target} if " . $rule->getDescription(), [
                        "{target}" => "[$target]",
                        "{permission}" => empty($permission) ? "do anything" : "[$permission]",
                    ]);
                } else {
                    $result[] = strtr("You can {permission} if " . $rule->getDescription(), [
                        "{target}" => "[$target]",
                        "{permission}" => empty($permission) ? "do anything" : "[$permission] it",
                    ]);
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


    /**
     * This function should return an array of associative arrays with grants.
     * Each param maybe NULL, which implies "don't care".
     * An empty string is not the same and must be matched exactly.
     * @param string|null $sourceName
     * @param string|null $sourceId
     * @param string|null $targetName
     * @param string|null $targetId
     * @param string|null $permission
     * @return Permission[]
     */
    abstract public function findExplicit(string $sourceName = null, string $sourceId = null, string $targetName = null, string $targetId = null, string $permission = null): array;


}