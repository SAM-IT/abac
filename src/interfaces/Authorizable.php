<?php


namespace SamIT\abac\interfaces;


interface Authorizable extends AuthorizableType
{
    /*
     * Get the unique identifier for this object (unique with respect to objects with the same name).
     * @return string
     */
    public function getId(): string;
}