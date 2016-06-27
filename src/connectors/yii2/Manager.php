<?php


namespace SamIT\ABAC\connectors\yii2;


class Manager extends \SamIT\ABAC\Manager implements \yii\rbac\AccessCheckerInterface, \yii\base\Configurable
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
     * @param string $sourceName
     * @param int $sourceId
     * @param string $targetName
     * @param int $targetId
     * @param string $permission
     * @return bool Whether the permission record was saved successfully.
     */
    protected function grantInternal($sourceName, $sourceId, $targetName, $targetId, $permission)
    {
        $perm = new Permission();
        $perm->source = $sourceName;
        $perm->source_id = $sourceId;
        $perm->target = $targetName;
        $perm->target_id = $targetId;
        $perm->permission = $permission;
        return $perm->save();
    }

    /**
     * @param string $sourceName
     * @param int $sourceId
     * @param string $targetName
     * @param int $targetId
     * @param string $permission
     */
    protected function revokeInternal($sourceName, $sourceId, $targetName, $targetId, $permission)
    {
        Permission::deleteAll([
            'source_model' => $sourceName,
            'source_id' => $sourceId,
            'target_model' => $targetId,
            'target_id' => $targetId,
            'permission' => $permission
        ]);
        return !$this->isAllowedExplicit($sourceName, $sourceId, $targetName, $targetId, $permission);
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
     * @param string $sourceName
     * @param int $sourceId
     * @param string $targetName
     * @param int $targetId
     * @param $permission
     */
    protected function isAllowedExplicit($sourceName, $sourceId, $targetName, $targetId, $permission)
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