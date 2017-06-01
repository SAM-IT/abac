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
 * @property int $source_id
 * @property string $target
 * @property int $target_id
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



    /**
     * Checks if a set of sources is allowed $permission on the $target.
     * @param ActiveRecordInterface $source The source object.
     * @param ActiveRecordInterface $target The target objects.
     * @param $permission The permission to be checked.
     * @return boolean
     * @throws \Exception
     */
    public static function isAllowed(ActiveRecordInterface $source, ActiveRecordInterface $target, $permission)
    {
        if ($target->primaryKey === null) {
            throw new \Exception("Invalid record.");
        }
        return self::isAllowedById(get_class($source), $source->getPrimaryKey(), get_class($target), $target->getPrimaryKey(), $permission);
    }


    private static function getCache($sourceModel, $sourceId, $targetModel, $targetId, $permission)
    {
        if (!isset($targetId)) {
            throw new \Exception('targetId is required');
        }
        \Yii::info("Checking from cache: $sourceModel [$sourceId] --> $targetModel [$targetId]");
        $key = md5($sourceModel . $sourceId . $targetModel . $targetId . $permission);

        return isset(self::$cache[$key]) ? self::$cache[$key] : (isset(self::$cache[$sourceModel . $sourceId]) ? false : null);
    }

    private static function setCache($sourceModel, $sourceId, $targetModel, $targetId, $permission, $value)
    {
        $key = md5($sourceModel . $sourceId . $targetModel . $targetId . $permission);
        self::$cache[$key] = $value;
    }


    public static function isAllowedById($sourceModel, $sourceId, $targetModel, $targetId, $permission)
    {
        self::loadCache($sourceModel, $sourceId);

        if (null === $result = self::getCache($sourceModel, $sourceId, $targetModel, $targetId, $permission)) {
            $levels = self::permissionLevels();
            if (isset($levels[$permission])) {
                $permissionLevel = self::permissionLevels()[$permission];
                $permissions = array_keys(array_filter(self::permissionLevels(), function ($value) use ($permissionLevel) {
                    return $value >= $permissionLevel;
                }));
            } else {
                $permissions = [$permission];
            }

            $query = self::find();
            $query->orWhere(['source' => $sourceModel, 'source_id' => $sourceId]);
            $query->andWhere(['target' => $targetModel, 'target_id' => $targetId, 'permission' => $permissions]);

            $result = $query->exists();
            self::setCache($sourceModel, $sourceId, $targetModel, $targetId, $permission, $result);
        }

        return $result;
    }


    /**
     * Checks if a $source is allowed $permission on any $targetClass instance.
     * @param ActiveRecordInterface $source
     * @param string $targetClass
     * @param string $permission
     */
    public static function anyAllowed(ActiveRecordInterface $source, $targetModel, $permission)
    {
        $query = self::find();
        $query->andWhere(['source' => get_class($source), 'source_id' => $source->id]);
        $query->andWhere(['target' => $targetModel, 'permission' => $permission]);

        return self::getDb()->cache(function($db) use ($query) {
            return $query->exists();
        }, 120);
    }

    public static function anyAllowedById($sourceModel, $sourceId, $targetModel, $permission)
    {
        $query = self::find();
        $query->andWhere(['source' => $sourceModel, 'source_id' => $sourceId]);
        $query->andWhere(['target' => $targetModel, 'permission' => $permission]);

        return self::getDb()->cache(function($db) use ($query) {
            return $query->exists();
        }, 120);
    }

    public static function permissionLabels()
    {
        return [
            self::PERMISSION_READ => \Yii::t('app', 'Read'),
            self::PERMISSION_WRITE => \Yii::t('app', 'Write/Read'),
            self::PERMISSION_SHARE => \Yii::t('app', 'Share/Write/Read'),
            self::PERMISSION_ADMIN => \Yii::t('app', 'Admin/Share/Write/Read'),
            self::PERMISSION_INSTANTIATE => \Yii::t('app', 'Instantiate')
        ];
    }

    public static function permissionLevels()
    {
        return [
            self::PERMISSION_READ => 0,
            self::PERMISSION_WRITE => 1,
            self::PERMISSION_SHARE => 2,
            self::PERMISSION_ADMIN => 3
        ];
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