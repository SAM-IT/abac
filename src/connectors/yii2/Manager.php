<?php


namespace SamIT\abac\connectors\yii2;


use SamIT\abac\AuthorizableDummy;

class Manager extends \SamIT\abac\Manager implements \yii\rbac\CheckAccessInterface, \yii\base\Configurable
{
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
            'source_name' => $sourceName,
            'source_id' => $sourceId,
            'target_name' => $targetId,
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
}