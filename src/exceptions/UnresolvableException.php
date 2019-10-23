<?php
declare(strict_types=1);

namespace SamIT\abac\exceptions;


class UnresolvableException extends \RuntimeException
{
    public function __construct(object $subject)
    {
        parent::__construct('Could not resolve object of class ' . get_class($subject));
    }

}