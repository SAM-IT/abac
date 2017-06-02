<?php


namespace SamIT\abac\rules;


use SamIT\abac\interfaces\Authorizable;
use SamIT\abac\Manager;
use SamIT\abac\interfaces\Rule;

/**
 * Class UserCanReadSelf
 * @package SamIT\abac\rules
 * Allow anyone to do dummy operations on anything.
 */
class AdminRole implements Rule
{



    /**
     * @inheritdoc
     */
    public function getDescription(): string
    {
        return "the {target} is you.";
    }

    /**
     * @inheritdoc
     */
    public function execute(Authorizable $source, Authorizable $target, \ArrayAccess $environment, Manager $manager, string $permission): bool
    {

        $sourceName = $source->getAuthName();
        $sourceId = $source->getId();
        foreach ($manager->admins as list($adminName, $adminId)) {
            if ($sourceName === $adminName && $sourceId == $adminId) {
                return true;
            }
        }
        return false;
    }

    /**
     * @inheritdoc*/
    public function getTargetNames(): array
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function getPermissions(): array
    {
        return [];
    }
}