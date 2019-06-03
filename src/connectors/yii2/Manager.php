<?php
declare(strict_types=1);

namespace SamIT\abac\connectors\yii2;


use SamIT\abac\AuthorizableDummy;
use SamIT\abac\interfaces\Authorizable;
use SamIT\abac\interfaces\Rule;
use yii\base\InvalidConfigException;
use yii\web\IdentityInterface;

class Manager extends \SamIT\abac\Manager implements \yii\rbac\CheckAccessInterface, \yii\base\Configurable
{
    /**
     * @var bool whether to log detailed information on decision making based on rules.
     */
    public $debug = YII_DEBUG;

    /**
     * @var string Name of a class that implements IdentityInterface
     */
    protected $userClass;

    public function isAllowed(Authorizable $source, Authorizable $target, string $permission)
    {
        $logName = function(Authorizable $authorizable) {
            return array_slice(explode('\\', $authorizable->getAuthName()), -1, 1)[0] . "({$authorizable->getId()})";
        };

        if ($this->debug) {
            $message = str_repeat('>', $this->depth) . strtr("[source] requesting [permission] permission on [target]", [
                '[permission]' => $permission,
                '[target]' => $logName($target),
                '[source]' => $logName($source)
            ]);
            if ($this->depth > 0) {
              \Yii::trace($message, 'abac');
            } else {
                \Yii::info($message, 'abac');
            }
        }
        $result = parent::isAllowed($source, $target, $permission);

        if ($this->debug) {
            $message = str_repeat('>', $this->depth) . ($result ? 'Allowing' : 'Denying') . strtr(" [source] [permission] permission on [target]", [
                '[permission]' => $permission,
                '[target]' => $logName($target),
                '[source]' => $logName($source)
            ]);
            if ($this->depth > 0) {
                \Yii::trace($message, 'abac');
            } else {
                \Yii::info($message, 'abac');
            }
        }
        return $result;

    }


    protected function execute(Rule $rule, Authorizable $source, Authorizable $target, string $permission): bool
    {
        $result = parent::execute($rule, $source, $target, $permission);

        if ($this->debug) {
            $ruleName = get_class($rule);
            $template = ($result ? 'TRUE:' : 'FALSE: ') . "You can [permission] the [target] if {$rule->getDescription()} ($ruleName)";
            $message = strtr($template, [
                '[permission]' => $permission,
                '[target]' => "{$target->getAuthName()}({$target->getId()})",
            ]);

            \Yii::trace($message, 'abac');
        }
        return $result;

    }


    public function __construct($config = [])
    {
        parent::__construct();
        foreach($config as $key => $value) {
            $this->$key = $value;
        }
        $this->init();
    }

    public function init()
    {
        if (!isset($this->userClass)) {
            throw new \yii\base\InvalidConfigException("userClass must be configured.");
        }
        if (!is_subclass_of($this->userClass, IdentityInterface::class, true)) {
            throw new InvalidConfigException("userClass must implement IdentityInterface");
        }
    }

    /**
     * @inheritdoc
     */
    protected function grantInternal(string $sourceName, string $sourceId, string $targetName, string $targetId, string $permission): void
    {
        try {
            $perm = new Permission();
            $perm->source_name = $sourceName;
            $perm->source_id = $sourceId;
            $perm->target_name = $targetName;
            $perm->target_id = $targetId;
            $perm->permission = $permission;
            if (!$perm->save()) {
                throw new \Exception("Failed to grant permission.");
            }
        } catch (\yii\db\Exception $e) {
            throw new \Exception("Failed to grant permission.", $e);
        }
    }

    /**
     * @inheritdoc
     */
    protected function revokeInternal(string $sourceName, string $sourceId, string $targetName, string $targetId, string $permission): void
    {
        Permission::deleteAll([
            'source_name' => $sourceName,
            'source_id' => $sourceId,
            'target_name' => $targetName,
            'target_id' => $targetId,
            'permission' => $permission
        ]);
    }



    /**
     * @return \ArrayObject
     */
    protected function getEnvironment()
    {
        return new \ArrayObject([
            'app' => \Yii::$app,
            'identity' => \Yii::$app->user->identity
        ]);
    }

    /**
     * @inheritdoc
     */
    protected function isAllowedExplicit(string $sourceName, string $sourceId, string $targetName, string $targetId, string $permission): bool
    {
        return Permission::isAllowedById($sourceName, $sourceId, $targetName, $targetId, $permission);
    }

    protected function getUser($id)
    {
        if (\Yii::$app->user->id == $id) {
            return \Yii::$app->user->identity;
        }
        return forward_static_call([$this->userClass, 'findOne'], $id);
    }

    public function checkAccess($userId, $permissionName, $params = [])
    {
        $source = $this->getUser($userId);
        return $this->isAllowed($source, $params['target'] ?? new AuthorizableDummy(), $permissionName);
    }


    /**
     * This function should return an array of associative arrays with grants.
     * Each param maybe NULL, which implies "don't care".
     * An empty string is not the same and must be matched exactly.
     * @param string|null $sourceName
     * @param string|null $sourceId
     * @param string|null $targetName
     * @param string|null $targetId
     * @param string|null $permission
     * @return array
     */
    public function findExplicit(
        string $sourceName = null,
        string $sourceId = null,
        string $targetName = null,
        string $targetId = null,
        string $permission = null
    ): array {

        // We use array_filter to remove NULL, not empty strings.
        return Permission::find()
            ->andWhere(array_filter([
                'source_name' => $sourceName,
                'source_id' => $sourceId,
                'target_name' => $targetName,
                'target_id' => $targetId,
                'permission' => $permission
            ], function($e) { return $e !== null; }))
            ->all();
    }
}