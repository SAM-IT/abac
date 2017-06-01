<?php


namespace SamIT\abac\connectors\yii2;


class Manager extends \SamIT\abac\Manager implements \yii\rbac\AccessCheckerInterface, \yii\base\Configurable
{
    /**
     * In yii2 rules are expected to be in protected, as is config.
     * @var string
     */
    protected $ruleDirectory = __DIR__ . '/../rules';
    /**
     * @var string The ActiveRecord class for users.
     */
    protected $userClass;

    /**
     * @return string[] The list of valid permission names. Can be static or could be retrieved from a database or
     * other dynamic storage.
     */
    public function permissionNames()
    {
        return [
            'read',
            'write'
        ];
    }

    public function __construct($config = [])
    {
        foreach($config as $key => $value) {
            $this->$config = $value;
        }
    }

    public function init()
    {
        if (!isset($this->userClass)) {
            throw new \yii\base\InvalidConfigException("userClass must be configured.");
        }
    }

    /**
     * @inheritdoc
     */
    protected function grantInternal(string $sourceName, string $sourceId, string $targetName, string $targetId, string $permission): void
    {
        $perm = new Permission();
        $perm->source = $sourceName;
        $perm->source_id = $sourceId;
        $perm->target = $targetName;
        $perm->target_id = $targetId;
        $perm->permission = $permission;
        if (!$perm->save()) {
            throw new \Exception("Failed to grant permission.");
        }
    }

    /**
     * @inheritdoc
     */
    protected function revokeInternal(string $sourceName, string $sourceId, string $targetName, string $targetId, string $permission): void
    {
        Permission::deleteAll([
            'source_model' => $sourceName,
            'source_id' => $sourceId,
            'target_model' => $targetId,
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
            'day' => 'Sunday',
        ]);
    }

    /**
     * @inheritdoc
     */
    protected function isAllowedExplicit(string $sourceName, string $sourceId, string $targetName, string $targetId, string $permission): boolean
    {
        return Permission::find()->where([
            'source_model' => $sourceName,
            'source_id' => $sourceId,
            'target_model' => $targetName,
            'target_id' => $targetId,
            'permission' => $permission
        ])->exists();

    }

    protected function getUser($id)
    {
        return call_user_func($this->userClass, 'findOne', $id);
    }

    public function checkAccess($userId, $permissionName, $params = [])
    {
        $source = $this->getUser($userId);
        $target = $params['target'];
        return $this->isAllowed($source, $target, $permissionName);
    }
}