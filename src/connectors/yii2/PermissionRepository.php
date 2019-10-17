<?php


namespace SamIT\abac\connectors\yii2;


use SamIT\abac\interfaces\Authorizable;
use SamIT\abac\interfaces\Grant;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

class PermissionRepository implements \SamIT\abac\interfaces\PermissionRepository
{
    /**
     * @var string
     */
    private $modelClass;

    /**
     * PermissionRepository constructor.
     */
    public function __construct(string $modelClass = Permission::class)
    {
        $this->modelClass = $modelClass;
    }

    /**
     * @inheritDoc
     */
    public function grant(Authorizable $source, Authorizable $target, string $permission): void
    {
        try {
            /** @var ActiveRecord $permission */
            $permission = new ($this->modelClass);
            $permission->source_id = $source->getId();
            $permission->source_name = $source->getAuthName();
            $permission->target_id = $target->getId();
            $permission->target_name = $target->getAuthName();
            if (!$permission->save()) {
                throw new \RuntimeException('Failed to save permission due to validation errors');
            }
        } catch (\RuntimeException $e) {
            throw $e;
        } catch (\Throwable $t) {
            throw new \RuntimeException('Failed to save permission');
        }
    }

    /**
     * @inheritDoc
     */
    public function revoke(Grant $grant): void
    {
        $source = $grant->getSource();
        $target = $grant->getTarget();
        $permission = $grant->getPermission();

        $this->modelClass::deleteAll([
            'source_id' => $source->getId(),
            'source_name' => $source->getAuthName(),
            'target_id' => $target->getId(),
            'target_name' => $target->getAuthName(),
            'permission' => $permission
        ]);
    }

    /**
     * @inheritDoc
     */
    public function check(Grant $grant): bool
    {
        $source = $grant->getSource();
        $target = $grant->getTarget();
        $permission = $grant->getPermission();

        return $this->modelClass::find()
            ->andWhere([
                'source_id' => $source->getId(),
                'source_name' => $source->getAuthName(),
                'target_id' => $target->getId(),
                'target_name' => $target->getAuthName(),
                'permission' => $permission
            ])
            ->exists();
    }

    /**
     * @inheritDoc
     */
    public function search(?Authorizable $source, ?Authorizable $target, ?string $permission): iterable
    {
        /** @var ActiveQuery $query */
        $query = $this->modelClass::find()
            ->andFilterWhere([
                'permission' => $permission,
                'source_id' => $source->getId() ?? null,
                'source_name' => $source->getAuthName() ?? null,
                'target_id' => $target->getId() ?? null,
                'target_name' => $target->getAuthName() ?? null,
            ]);

        foreach($query->each() as $permission) {
            $source = new \SamIT\abac\Authorizable($permission->source_id, $permission->source_name);
            $target = new \SamIT\abac\Authorizable($permission->target_id, $permission->target_name);
            yield new \SamIT\abac\Grant($source, $target, $permission->permission);
        }
    }
}