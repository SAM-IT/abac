<?php


namespace SamIT\abac\connectors\yii2;

trait ActiveRecordAuthorizableTrait
{
    abstract public function getAttribute($name);
    abstract public function hasAttribute($name);
    abstract static public function getTableSchema();
    abstract public function getPrimaryKey($asArray = false);


    public function getId(): string {
        $pk = $this->getPrimaryKey();
        return (is_array($pk) ? implode('|', $pk) : $pk) ?? "";
    }

    public function getAuthName(): string
    {
        return get_class($this);
    }
}