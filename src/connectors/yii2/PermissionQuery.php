<?php
namespace SamIT\abac\connectors\yii2;

use SamIT\abac\interfaces\Authorizable;
use yii\db\ActiveQuery;

class PermissionQuery extends ActiveQuery
{

    public function andWhereTarget(Authorizable $target)
    {
        return $this->andWhere([
            'target_id' => $target->getId(),
            'target_name' => $target->getAuthName()
        ]);
    }

    public function andWhereSource(Authorizable $source)
    {
        return $this->andWhere([
            'source_id' => $source->getId(),
            'source_name' => $source->getAuthName()
        ]);

    }

    public function andFilterSource(?Authorizable $source)
    {
        if (!isset($source)) {
            return $this;
        }
        return $this->andFilterWhere([
            'source_id' => $source->getId(),
            'source_name' => $source->getAuthName()
        ]);

    }

    public function andFilterTarget(?Authorizable $target)
    {
        if (!isset($target)) {
            return $this;
        }
        return $this->andFilterWhere([
            'target_id' => $target->getId(),
            'target_name' => $target->getAuthName()
        ]);
    }
}