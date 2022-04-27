<?php

declare(strict_types=1);

namespace SamIT\abac\rules;

use SamIT\abac\interfaces\AccessChecker;
use SamIT\abac\interfaces\Environment;
use SamIT\abac\interfaces\Rule;

/**
 * This rule allows a user to do something as long as they can do something else.
 */
class ImpliedPermission implements Rule
{
    /**
     * @var array<string, true> Keys contain the permissions, values a constant
     */
    private readonly array $implied;

    /**
     * @param string $requiredPermission
     * @param list<string> $impliedPermissions
     * @param list<string> $sourceNames,
     * @param list<string> $targetNames
     */
    public function __construct(
        private readonly string $requiredPermission,
        array $impliedPermissions,
        private readonly array $sourceNames = [],
        private readonly array $targetNames = []
    ) {
        $implied = [];
        foreach ($impliedPermissions as $permission) {
            $implied[$permission] = true;
        }
        $this->implied = $implied;
    }

    /**
     * "you can ... if [description]"
     */
    public function getDescription(): string
    {
        return "you can [{$this->requiredPermission}] it.";
    }

    public function execute(
        object $source,
        object  $target,
        string $permission,
        Environment $environment,
        AccessChecker $accessChecker
    ): bool {
        return isset($this->implied[$permission])
            && $accessChecker->check($source, $target, $this->requiredPermission);
    }

    /**
     * @return list<string>
     */
    public function getTargetNames(): array
    {
        return $this->targetNames;
    }

    /**
     * @return list<string>
     */
    public function getPermissions(): array
    {
        return array_keys($this->implied);
    }

    /**
     * @return list<string>
     */
    public function getSourceNames(): array
    {
        return $this->sourceNames;
    }
}
