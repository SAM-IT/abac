<?php

declare(strict_types=1);

namespace SamIT\abac\exceptions;

use Throwable;

class UnresolvableException extends \RuntimeException
{
    /**
     * Constructor is final so that we can safely use the static `forSubject` constructor for subclasses
     */
    final public function __construct(string $message = "", int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public static function forSubject(object $subject): static
    {
        return new static('Could not resolve object of class ' . get_class($subject));
    }
}
