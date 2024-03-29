<?php

declare(strict_types=1);

namespace SamIT\abac\exceptions;

/**
 * Class NestingException
 * @codeCoverageIgnore
 */
class NestingException extends \RuntimeException
{
    public function __construct(int $depth)
    {
        parent::__construct("Max nesting depth of {$depth} exceeded");
    }
}
