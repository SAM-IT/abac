<?php


namespace SamIT\abac\interfaces;


interface AccessChecker
{
    /**
     * @param Authorizable $source
     * @param Authorizable $target
     * @param string $permission
     * @param Environment $environment
     * @return bool whether Source has been granted Permission to Target
     */
    public function check(
        Authorizable $source,
        Authorizable $target,
        string $permission
    ): bool;
}