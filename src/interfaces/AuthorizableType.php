<?php


namespace SamIT\abac\interfaces;

interface AuthorizableType
{
    /*
     * Get the class name for authorization objects of this type.
     * Mainly used to allow subclassing without changing the name.
     */
    public function getAuthName(): string;
}
