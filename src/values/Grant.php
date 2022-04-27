<?php

declare(strict_types=1);

namespace SamIT\abac\values;

use SamIT\abac\interfaces\Authorizable;

/**
 * Simple value object
 */
final class Grant implements \SamIT\abac\interfaces\Grant
{
    public function __construct(
        private readonly Authorizable $source,
        private readonly Authorizable $target,
        private readonly string $permission
    ) {
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
