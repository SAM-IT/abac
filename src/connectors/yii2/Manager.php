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
            ->select(['source_name', 'source_id', 'target_name', 'target_id', 'permission'])
            ->all();
    }
}