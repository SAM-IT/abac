<?php

declare(strict_types=1);

namespace SamIT\abac\helpers;

use SamIT\abac\interfaces\Authorizable;

final class AuthorizableSerializer
{
    public function __construct(private readonly string $separator = '|')
    {
    }

    public function __invoke(Authorizable $authorizable): string
    {
        return $this->serialize($authorizable);
    }

    public function serialize(Authorizable $authorizable): string
    {
        return "{$authorizable->getAuthName()}{$this->separator}{$authorizable->getId()}";
    }
}
