<?php

namespace SamIT\abac\connectors\yii2;

use prime\models\ActiveRecord;
use prime\models\ar\Project;
use prime\models\ar\User;
use yii\db\ActiveRecordInterface;

/**
 * Class Permission
 * @package app\models
 * @property string $permission
 * @property string $source
 * @property string $source_id
 * @property string $target
 * @property string $target_id
 */
class Permission extends \yii\db\ActiveRecord
{
    // Cache for the results for the anyAllowed lookup.
    private static $anyCache = [];
    // Cache for the results for the isAllowed loookup.
    private static $cache = [];

    const PERMISSION_READ = 'read';
    const PERMISSION_WRITE = 'write';
    const PERMISSION_SHARE = 'share';
    const PERMISSION_ADMIN = 'admin';
    const PERMISSION_INSTANTIATE = 'instantiate';


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
            ])->all() as $grant) {
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
    public static function anyAllowed(ActiveRecordInterface $source, $targetName, $permission)
    {
        $query = self::find();
        $query->andWhere(['source_name' => get_class($source), 'source_id' => $source->id]);
        $query->andWhere(['target_name' => $targetName, 'permission' => $permission]);

        return self::getDb()->cache(function($db) use ($query) {
            return $query->exists();
        }, 120);
    }

    public static function anyAllowedById($sourceName, $sourceId, $targetName, $permission)
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
            [['source', 'source_id', 'target', 'target_id', 'permission'], 'required'],
            [['source', 'source_id', 'target', 'target_id'], 'unique', 'targetAttribute' => ['source', 'source_id', 'target', 'target_id']],
            [['permission'], 'in', 'range' => array_keys(self::permissionLabels())]
        ];
    }

    public static function tableName()
    {
        return '{{%permission}}';
    }


}