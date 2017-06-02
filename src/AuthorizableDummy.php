<?php


namespace SamIT\abac;


use SamIT\abac\interfaces\Authorizable;

class AuthorizableDummy implements Authorizable
{

    public function getId(): string
    {
        return "";
    }

    public function getAuthName(): string
    {
        return "";
    }


}