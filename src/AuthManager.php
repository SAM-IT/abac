<?php


namespace SamIT\abac;


use SamIT\abac\interfaces\AccessChecker;
use SamIT\abac\interfaces\Authorizable;
use SamIT\abac\interfaces\Environment;
use SamIT\abac\interfaces\PermissionRepository;
use SamIT\abac\interfaces\Resolver;
use SamIT\abac\interfaces\RuleEngine;

class AuthManager implements AccessChecker
{
    public const MAX_DEPTH = 400;
    /**
     * @var PermissionRepository
     */
    private $permissionRepository;

    /**
     * @var RuleEngine
     */
    private $ruleEngine;

    /**
     * @var Resolver
     */
    private $resolver;

    /**
     * @var Environment
     */
    private $environment;

    private $depth = 0;

    public function __construct(
        RuleEngine $ruleEngine,
        PermissionRepository $permissionRepository,
        Resolver $resolver,
        Environment $environment
    ) {
        $this->ruleEngine = $ruleEngine;
        $this->permissionRepository = $permissionRepository;
        $this->resolver = $resolver;
        $this->environment = $environment;
    }

    /**
     * @param object $source
     * @param object $target
     * @param string $permission
     * @return bool
     */
    public function resolveAndCheck(
        object $source,
        object $target,
        string $permission
    ) {
        $sourceAuthorizable = $this->resolver->fromSubject($source);
        $targetAuthorizable = $this->resolver->fromSubject($target);
        return $this->check($sourceAuthorizable, $targetAuthorizable, $permission);
    }

    /**
     * @inheritDoc
     */
    public function check(
        Authorizable $source,
        Authorizable $target,
        string $permission
    ): bool {
        $this->depth++;
        try {
            if ($this->depth > self::MAX_DEPTH) {
                throw new \RuntimeException('Max nesting depth exceeded');
            }
            return $this->permissionRepository->check(new Grant($source, $target, $permission))
                || $this->ruleEngine->check(
                    $source,
                    $target,
                    $permission,
                    $this->environment,
                    $this
                );
        } finally {
            $this->depth--;
        }
    }
}