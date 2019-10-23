<?php
declare(strict_types=1);

namespace SamIT\abac\values;


use SamIT\abac\interfaces\Authorizable;

/**
 * Simple value object
 * @package SamIT\abac
 */
final class Grant implements \SamIT\abac\interfaces\Grant
{
    /**
     * @var Authorizable
     */
    private $source;

    /**
     * @var Authorizable
     */
    private $target;

    /**
     * @var string
     */
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