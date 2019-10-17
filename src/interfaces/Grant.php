<?php


namespace SamIT\abac\interfaces;


interface Grant
{
    public function getSource(): Authorizable;
    public function getTarget(): Authorizable;

    public function getPermission(): string;
}