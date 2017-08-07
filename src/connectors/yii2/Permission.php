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
    // Cache for the results for the anyAllowed lookup.
    private static $anyCache = [];
    // Cache for the results for the isAllowed loookup.
    private static $cache = [];

    public function attributeLabels()
    {
        return [
            'permissionLabel' => \Yii::t('app', 'Permission')
        ];
    }



    private static function getCache($sourceName, $sourceId, $targetName, $targetId, $permission)
    {
        if (!isset($targetId)) {
            throw new \Exception('targetId is required');
        }
        \Yii::info("Checking from cache: $sourceName [$sourceId] --> $targetName [$targetId]");
        $key = self::cacheKey($sourceName, $sourceId, $targetName, $targetId, $permission);
        return self::$cache[$key] ?? self::$cache["$sourceName|$sourceId"] ?? null;
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

        if (null === $result = self::getCache($sourceName, $sourceId, $targetName, $targetId, $permission)) {
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
        $query = self::find();
        $query->andWhere([
            'source_name' => $source->getAuthName(),
            'source_id' => $source->getId(),
            'target_name' => $targetName,
            'permission' => $permission
        ]);
        $query->andWhere([]);

        return self::getDb()->cache(function($db) use ($query) {
            return $query->exists();
        }, 120);
    }

    public static function anySourceAllowed(Authorizable $target, $sourceName = null, $permission = null): bool
    {
        $query = self::find();
        $query->andWhere([
            'target_name' => $target->getAuthName(),
            'target_id' => $target->getId(),

        ]);
        $query->andFilterWhere(['source_name' => $sourceName]);
        $query->andFilterWhere(['permission' => $permission]);

        return self::getDb()->cache(function($db) use ($query) {
            return $query->exists();
        }, 120);
    }

    public static function anyAllowedById($sourceName, $sourceId, $targetName, $permission): bool
    {
        $query = self::find();
        $query->andWhere(['source_name' => $sourceName, 'source_id' => $sourceId]);
        $query->andWhere(['target_name' => $targetName, 'permission' => $permission]);

        return self::getDb()->cache(function($db) use ($query) {
            return $query->exists();
        }, 120);
    }

    public function rules()
    {
        return [
            [['source_name', 'source_id', 'target_name', 'target_id', 'permission'], 'required'],
            [['source_name', 'source_id', 'target_name', 'target_id'], 'unique', 'targetAttribute' => ['source_name', 'source_id', 'target_name', 'target_id']],
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
}