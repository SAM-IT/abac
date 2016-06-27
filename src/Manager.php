<?php

namespace SamIT\ABAC;

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
    private $map;

    protected $ruleDirectory = __DIR__ . '/rules';

    const PERMISSION_READ = 'read';
    const PERMISSION_WRITE = 'write';
    const PERMISSION_SHARE = 'share';
    const PERMISSION_ADMIN = 'admin';
    const PERMISSION_INSTANTIATE = 'instantiate';

    /**
     * @return string[] The list of valid permission names. Can be static or could be retrieved from a database or
     * other dynamic storage.
     */
    abstract public function permissionNames();


    public function grant(Authorizable $source, Authorizable $target, $permission)
    {
        if (!in_array($permission, $this->permissionNames())) {
            throw new \Exception("Unknown permission.");
        }
        $this->grantInternal($source->getAuthName(), $source->getId(), $target->getAuthName(), $target->getId(), $permission);
    }

    /**
     * @param string $sourceName
     * @param int $sourceId
     * @param string $targetName
     * @param int $targetId
     * @param string $permission
     */
    abstract protected function grantInternal($sourceName, $sourceId, $targetName, $targetId, $permission);

    /**
     * @param string $sourceName
     * @param int $sourceId
     * @param string $targetName
     * @param int $targetId
     * @param string $permission
     */
    abstract protected function revokeInternal($sourceName, $sourceId, $targetName, $targetId, $permission);

    public function grantById($sourceName, $sourceId, $targetName, $targetId, $permission)
    {
        if (!in_array($permission, $this->permissionNames())) {
            throw new \Exception("Unknown permission.");
        }

        if (!is_subclass_of($sourceName, Authorizable::class)) {
            throw new \Exception("Cannot grant access for unknown class: " . $sourceName);
        }

        if (!is_subclass_of($targetName, Authorizable::class)) {
            throw new \Exception("Cannot grant access to unknown class: " . $targetName);
        }

        $this->grantInternal($sourceName, $sourceId, $targetName, $targetId);

    }

    public function revoke(Authorizable $source, Authorizable $target, $permission)
    {
        if (!in_array($permission, $this->permissionNames())) {
            throw new \Exception("Unknown permission.");
        }
        $this->revokeInternal($source->getAuthName(), $source->getId(), $target->getAuthName(), $target->getId(), $permission);
    }

    public function revokeById($sourceName, $sourceId, $targetName, $targetId, $permission)
    {
        if (!in_array($permission, $this->permissionNames())) {
            throw new \Exception("Unknown permission.");
        }

        if (!is_subclass_of($sourceName, Authorizable::class)) {
            throw new \Exception("Cannot grant access for unknown class: " . $sourceName);
        }

        if (!is_subclass_of($targetName, Authorizable::class)) {
            throw new \Exception("Cannot grant access to unknown class: " . $targetName);
        }

        $this->revokeInternal($sourceName, $sourceId, $targetName, $targetId);
    }


    protected function buildRules()
    {
        $namespace = 'SamIT\\ABAC\\rules';
        // Map
        $map = [];

        // Scan directory.
        foreach(scandir($this->ruleDirectory) as $file) {
            if (substr_compare($file, '.php', -4, 4, false) === 0) {
                $fqc = $namespace . "\\" . substr($file, 0, -4);
                /** @var Rule $rule */
                $rule = new $fqc();
                if (!empty($rule->getTargetTypes())) {
                    foreach ($rule->getTargetTypes() as $class) {
                        $key = "{$rule->getPermissionName()}|$class";
                        if (isset($map[$key])) {
                            $map[$key][] = $rule;
                        } else {
                            $map[$key] = [$rule];
                        }
                    }
                } else {
                    $key = "{$rule->getPermissionName()}|";
                    if (isset($map[$key])) {
                        $map[$key][] = $rule;
                    } else {
                        $map[$key] = [$rule];
                    }
                }
            }
        }

        $this->map = $map;
    }

    public function __construct()
    {
        $this->buildRules();
    }

    /**
     * @return \ArrayObject
     */
    abstract protected function getEnvironment();

    public function isAllowed(Authorizable $source, Authorizable $target, $permission)
    {
        if (!in_array($permission, $this->permissionNames())) {
            throw new \Exception("Unknown permission.");
        }

        if ($this->isAllowedExplicit($source->getAuthName(), $source->getId(), $target->getAuthName(), $target->getId(), $permission)) {
            return true;
        }
        // Check specific rules
        $key = "$permission|{$target->getAuthName()}";
        if (isset($this->map[$key])) {
            /** @var Rule $rule */
            foreach($this->map[$key] as $rule) {
                if ($rule->execute($source, $target, $this->getEnvironment(), $this)) {
                    return true;
                }
            }
        }

        // Check generic rules.
        $key = "$permission|";
        if (isset($this->map[$key])) {
            /** @var Rule $rule */
            foreach($this->map[$key] as $rule) {
                if ($rule->execute($source, $target, $this->getEnvironment(), $this)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param string $sourceName
     * @param int $sourceId
     * @param string $targetName
     * @param int $targetId
     * @param $permission
     */
    abstract protected function isAllowedExplicit($sourceName, $sourceId, $targetName, $targetId, $permission);


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
        foreach($this->map as $key => $rules) {
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


}