<?php


namespace SamIT\abac\interfaces;


interface Authorizable
{
    /*
     * Get the unique id for this object (unique with respect to objects with the same name).
     * @return string
     */
    public function getId(): string;

    /*
     * Get the class name for authorization objects of this type.
     * Mainly used to allow subclassing without changing the name.
     * @return string
     */
    public function getAuthName(): string;

}