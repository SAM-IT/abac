<?php


namespace SamIT\abac\rules;

use SamIT\abac\interfaces\AccessChecker;
use SamIT\abac\interfaces\Authorizable;
use SamIT\abac\interfaces\Environment;
use SamIT\abac\interfaces\Rule;

/**
 * This rule allows a user to do something as long as they can do something else.
 */
class ImpliedPermission implements Rule
{
    /**
     * @var string
     */
    private $required;

    /**
     * @var array|true[string] Keys contain the permissions, values a constant
     */
    private $implied = [];

    public function __construct(string $requiredPermission, array $impliedPermission)
    {
        $this->required = $requiredPermission;
        foreach ($impliedPermission as $permission) {
            $this->implied[$permission] = true;
        }
    }

    /**
     * @inheritdoc
     * "you can ... if [description]"
     * @codeCoverageIgnore
     */
    public function getDescription(): string
    {
        return "you can [{$this->required}] it.";
    }

    /**
     * @param Authorizable $source
     * @param \SamIT\abac\interfaces\Authorizable $target
     * @return boolean
     */
    public function execute(
        object $source,
        object  $target,
        string $permission,
        Environment $environment,
        AccessChecker $accessChecker
    ): bool {
        return isset($this->implied[$permission])
            && $accessChecker->check($source, $target, $this->required);
    }

    /**
     * @inheritDoc
     * @codeCoverageIgnore
     */
    public function getTargetNames(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     * @codeCoverageIgnore
     */
    public function getPermissions(): array
    {
        return array_keys($this->implied);
    }

    /**
     * @inheritDoc
     * @codeCoverageIgnore
     */
    public function getSourceNames(): array
    {
        return [];
    }
}
