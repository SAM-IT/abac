<?php
declare(strict_types=1);

namespace SamIT\abac\connectors\yii2;

use SamIT\abac\interfaces\Authorizable;
use SamIT\abac\interfaces\Resolver;
use yii\db\ActiveRecord;
use yii\db\ActiveRecordInterface;

class ActiveRecordResolver implements Resolver
{
    /**
     * @inheritDoc
     */
    public function fromSubject(object $object): ?Authorizable
    {
        if ($object instanceof ActiveRecord) {
            $id = implode('|', $object->getPrimaryKey(true));
            $name = get_class($object);
            return new \SamIT\abac\Authorizable($id, $name);
        } else {
            return null;
        }

    }

    /**
     * @inheritDoc
     */
    public function toSubject(Authorizable $authorizable): ?object
    {
        $name = $authorizable->getAuthName();
        if (class_exists($name) && is_subclass_of($name, ActiveRecordInterface::class, true)) {
            return $name::findOne(explode('|', $authorizable->getId()));
        } else {
            return null;
        }
    }
}