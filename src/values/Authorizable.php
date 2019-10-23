<?php
declare(strict_types=1);

namespace SamIT\abac\values;

/**
 * Simple immutable Authorizable implementation
 */
final class Authorizable implements \SamIT\abac\interfaces\Authorizable
{
    private $id;
    private $name;

    public function __construct(string $id, string $name)
    {
        $this->id = $id;
        $this->name = $name;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getAuthName(): string
    {
        return $this->name;
    }
}