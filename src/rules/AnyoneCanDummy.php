<?php


namespace SamIT\abac\rules;


use SamIT\abac\interfaces\AccessChecker;
use SamIT\abac\interfaces\Authorizable;
use SamIT\abac\interfaces\Environment;
use SamIT\abac\interfaces\SimpleRule;

/**
 * Class UserCanReadSelf
 * @package SamIT\abac\rules
 * Allow anyone to do dummy operations on anything.
 */
class AnyoneCanDummy implements SimpleRule
{
    /**
     * @inheritdoc
     */
    public function getDescription(): string
    {
        return "true";
    }

    /**
     * @param \SamIT\abac\interfaces\Authorizable $source
     * @param \SamIT\abac\interfaces\Authorizable $target
     * @return boolean
     */
    public function execute(
        Authorizable $source,
        Authorizable $target,
        string $permission,
        Environment $environment,
        AccessChecker $accessChecker
    ): bool {
        return $permission === 'dummy';
    }
}