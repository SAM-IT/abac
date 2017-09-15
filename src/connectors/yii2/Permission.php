<?php

namespace SamIT\abac\connectors\yii2;

use prime\models\ActiveRecord;
use prime\models\ar\Project;
use prime\models\ar\User;
use SamIT\abac\interfaces\Authorizable;
use SamIT\abac\interfaces\Permission as PermissionInterface;
use yii\db\ActiveRecordInterface;
use yii\validators\StringValidator;

/**
 * Class Permission
 * @package app\models
 * @property string $permission
 * @property string $source_name
 * @property string $source_id
 * @property string $target_name
 * @property string $target_id
 */
class Permission extends \yii\db\ActiveRecord implements PermissionInterface, Authorizable
{
    use ActiveRecordAuthorizableTrait;

        // Cache for the results for the isAllowed loookup.
    private static $cache = [];

    public function attributeLabels()
    {
        return [
            'permissionLabel' => \Yii::t('app', 'Permission')
        ];
    }



    private static function getCache($sourceName, $sourceId, $targetName, $targetId, $permission): ?bool
    {
        if (!isset($targetId)) {
            throw new \Exception('targetId is required');
        }
        if (\Yii::$app->authManager->debug) {
            \Yii::info("Checking from cache: $sourceName [$sourceId] --($permission)--> $targetName [$targetId]", 'abac');
        }
        $key = self::cacheKey($sourceName, $sourceId, $targetName, $targetId, $permission);
        $result = self::$cache[$key] ?? self::$cache["$sourceName|$sourceId"] ?? null;
        if (\Yii::$app->authManager->debug) {
            \Yii::info("Returning: " . ($result ? "true" : (is_null($result) ? "NULL" : "false")), 'abac');
        }
        return $result;
    }

    private static function cacheKey($sourceName, $sourceId, $targetName, $targetId, $permission): string
    {
        return implode('|', [$sourceName, $sourceId, $targetName, $targetId, $permission]);
    }

    private static function setCache(
        string $sourceName,
        string $sourceId,
        string $targetName,
        string $targetId,
        string $permission,
        bool $value
    ) {
        self::$cache[self::cacheKey($sourceName, $sourceId, $targetName, $targetId, $permission)] = $value;
    }

    private static function loadCache($sourceName, $sourceId)
    {
        if (!isset(self::$cache["$sourceName|$sourceId"])) {
            foreach (self::find()->where([
                'source_name' => $sourceName,
                'source_id' => $sourceId
            ])->each() as $grant) {
                self::setCache($sourceName, $sourceId, $grant->target_name, $grant->target_id, $grant->permission,
                    true);
            };
            self::$cache["$sourceName|$sourceId"] = false;
        }

    }

    public static function isAllowedById($sourceName, $sourceId, $targetName, $targetId, $permission)
    {
        self::loadCache($sourceName, $sourceId);

        if (null === ($result = self::getCache($sourceName, $sourceId, $targetName, $targetId, $permission))) {
            $query = self::find()->where([
                'source_name' => $sourceName,
                'source_id' => $sourceId,
                'target_name' => $targetName,
                'target_id' => $targetId,
                'permission' => $permission
            ]);

            $result = $query->exists();
            self::setCache($sourceName, $sourceId, $targetName, $targetId, $permission, $result);
        }

        return $result;
    }


    /**
     * Checks if a $source is allowed $permission on any $targetClass instance.
     * @param ActiveRecordInterface $source
     * @param string $targetClass
     * @param string $permission
     */
    public static function anyAllowed(Authorizable $source, $targetName, $permission): bool
    {
        return self::anyAllowedById($source->getAuthName(), $source->getId(), $targetName, $permission);
    }

    private static $anySourceAllowedCache = [];

    public static function anySourceAllowed(Authorizable $target, $sourceName = null, $permission = null): bool
    {
        $key  = implode('|', [$target->getAuthName(), $target->getId(), $sourceName, $permission]);

        if (!isset(self::$anySourceAllowedCache[$key])) {
            $query = self::find();
            $query->andWhere([
                'target_name' => $target->getAuthName(),
                'target_id' => $target->getId(),

            ]);
            $query->andFilterWhere(['source_name' => $sourceName]);
            $query->andFilterWhere(['permission' => $permission]);

            self::$anySourceAllowedCache[$key] = $query->exists();
        }

        return self::$anySourceAllowedCache[$key];
    }

    // Cache for the results for the anyAllowed lookup.
    private static $anyAllowedCache = [];

    public static function anyAllowedById(string $sourceName, string $sourceId, string $targetName, string $permission): bool
    {
        $key  = implode('|', [$sourceName, $sourceId, $targetName, $permission]);
        if (!isset(self::$anyAllowedCache[$key])){
            $query = self::find();
            $query->andWhere(['source_name' => $sourceName, 'source_id' => $sourceId]);
            $query->andWhere(['target_name' => $targetName, 'permission' => $permission]);
            self::$anyAllowedCache[$key] = self::getDb()->cache(function($db) use ($query) {
                return $query->exists();
            }, 120);
        }

        return self::$anyAllowedCache[$key];
    }

    public function rules()
    {
        return [
            [['source_name', 'source_id', 'target_name', 'target_id', 'permission'], 'required'],
            [['source_name', 'source_id', 'target_name', 'target_id', 'permission'], 'unique', 'targetAttribute' => ['source_name', 'source_id', 'target_name', 'target_id', 'permission']],
            [['permission'], StringValidator::class, 'min' => 1]
        ];
    }

    public static function tableName()
    {
        return '{{%permission}}';
    }


    public function getSourceName(): string
    {
        return $this->getAttribute('source_name');
    }

    public function getSourceId(): string
    {
        return $this->getAttribute('source_id');
    }

    public function getTargetName(): string
    {
        return $this->getAttribute('target_name');
    }

    public function getTargetId(): string
    {
        return $this->getAttribute('target_id');
    }

    public function getSource(): ?Authorizable
    {
        return $this->getSourceName()::findOne($this->getSourceId());
    }

    public function getTarget(): ?Authorizable
    {
        return $this->getTargetName()::findOne($this->getTargetId());
    }

    public static function find()
    {
        return \Yii::createObject(PermissionQuery::className(), [get_called_class()]);
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        // Clear all caches.
        self::$anyAllowedCache = [];
        self::$anySourceAllowedCache = [];
        self::$cache = [];
    }


}