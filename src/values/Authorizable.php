<?php

declare(strict_types=1);

namespace SamIT\abac\values;

/**
 * Simple immutable Authorizable implementation
 */
final class Authorizable implements \SamIT\abac\interfaces\Authorizable
{
    public function __construct(private readonly string $id, private readonly string $name)
    {
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
