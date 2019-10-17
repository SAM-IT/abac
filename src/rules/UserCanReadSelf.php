<?php


namespace SamIT\abac\rules;


use SamIT\abac\interfaces\AccessChecker;
use SamIT\abac\interfaces\Authorizable;
use SamIT\abac\interfaces\Environment;
use SamIT\abac\Manager;
use SamIT\abac\interfaces\Rule;


class UserCanReadSelf implements Rule
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
    public function execute(
        Authorizable $source,
        Authorizable $target,
        string $permission,
        Environment $environment,
        AccessChecker $accessChecker
    ): bool {
        return $source->getAuthName() === 'user'
            && $source->getAuthName() === $target->getAuthName()
            && $source->getId() === $target->getId();
    }

    public function getTargetNames(): array
    {
        return ['user'];
    }

    public function getPermissions(): array
    {
        return [Manager::PERMISSION_READ];
    }

    /**
     * @inheritDoc
     */
    public function getSourceNames(): array
    {
        return $this->getSourceNames();
    }
}