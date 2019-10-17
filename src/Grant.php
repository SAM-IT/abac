<?php
declare(strict_types=1);

namespace SamIT\abac;


use SamIT\abac\interfaces\Authorizable;

final class Grant implements \SamIT\abac\interfaces\Grant
{
    private $source;
    private $target;
    private $permission;

    public function __construct(Authorizable $source, Authorizable $target, string $permission)
    {
        $this->source = $source;
        $this->target = $target;
        $this->permission = $permission;
    }

    public function getSource(): Authorizable
    {
        return $this->source;
    }

    public function getTarget(): Authorizable
    {
        return $this->target;
    }

    public function getPermission(): string
    {
        return $this->permission;
    }
}