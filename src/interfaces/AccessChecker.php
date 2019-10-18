<?php


namespace SamIT\abac\interfaces;


interface AccessChecker
{
    /**
     * @param object $source
     * @param object $target
     * @param string $permission
     * @param Environment $environment
     * @return bool whether Source has been granted Permission to Target
     */
    public function check(
        object $source,
        object $target,
        string $permission
    ): bool;
}