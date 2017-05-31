<?php


namespace SamIT\ABAC\interfaces;


interface Authorizable
{
    /*
     * Get the unique id for this object (unique with respect to objects with the same name).
     * @return int
     */
    public function getId();

    /*
     * Get the class name for authorization objects of this type.
     * Mainly used to allow subclassing without changing the name.
     * @return string
     */
    public function getAuthName();

    /**
     * @param string $name The name of the attribute to get.
     * @return mixed The value of the attribute.
     * @throws \InvalidArgumentException if the attribute name is not valid for the object.
     */
    public function getAuthAttribute($name);
}