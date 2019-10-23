<?php
declare(strict_types=1);

namespace SamIT\abac;


use SamIT\abac\exceptions\NestingException;
use SamIT\abac\exceptions\UnresolvableSourceException;
use SamIT\abac\exceptions\UnresolvableTargetException;
use SamIT\abac\interfaces\AccessChecker;
use SamIT\abac\interfaces\Environment;
use SamIT\abac\interfaces\PermissionRepository;
use SamIT\abac\interfaces\Resolver;
use SamIT\abac\interfaces\RuleEngine;
use SamIT\abac\values\Grant;

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
    private $partialResults = [];

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

    private function storePartial(Grant $grant, bool $result)
    {
        if ($this->depth > 1) {

            $source = $grant->getSource();
            $target = $grant->getTarget();
            $key = "{$source->getAuthName()}|{$source->getId()}|{$target->getAuthName()}|{$target->getId()}|{$grant->getPermission()}";
            $this->partialResults[$key] = $result;
        }
    }

    private function getPartial(Grant $grant): ?bool
    {
        if ($this->depth > 1) {
            $source = $grant->getSource();
            $target = $grant->getTarget();
            $key = "{$source->getAuthName()}|{$source->getId()}|{$target->getAuthName()}|{$target->getId()}|{$grant->getPermission()}";
            return $this->partialResults[$key] ?? null;
        }
        return null;
    }

    /**
     * @inheritDoc
     */
    public function check(
        object $source,
        object $target,
        string $permission
    ): bool {
        $this->depth++;
        try {
            if ($this->depth > self::MAX_DEPTH) {
                throw new NestingException($this->depth);
            }

            $sourceAuthorizable = $this->resolver->fromSubject($source);
            if (!isset($sourceAuthorizable)) {
                throw new UnresolvableSourceException($source);
            }

            $targetAuthorizable = $this->resolver->fromSubject($target);
            if (!isset($targetAuthorizable)) {
                throw new UnresolvableTargetException($target);
            }

            $grant = new Grant($sourceAuthorizable, $targetAuthorizable, $permission);

            if (null === $result = $this->getPartial($grant)) {
                $result = $this->permissionRepository->check($grant) || $this->ruleEngine->check(
                    $source,
                    $target,
                    $permission,
                    $this->environment,
                    $this
                );
                $this->storePartial($grant, $result);
            }
            return $result;
        } finally {
            $this->depth--;
            if ($this->depth === 0) {
                $this->partialResults = [];
            }
        }
    }

    public function grant(object $source, object $target, string $permission)
    {
        $sourceAuthorizable = $this->resolver->fromSubject($source);
        if (!isset($sourceAuthorizable)) {
            throw new UnresolvableSourceException($source);
        }

        $targetAuthorizable = $this->resolver->fromSubject($target);
        if (!isset($targetAuthorizable)) {
            throw new UnresolvableTargetException($target);
        }

        $grant = new Grant($sourceAuthorizable, $targetAuthorizable, $permission);

        $this->permissionRepository->grant($grant);
    }

    public function revoke(object $source, object $target, string $permission)
    {
        $sourceAuthorizable = $this->resolver->fromSubject($source);
        if (!isset($sourceAuthorizable)) {
            throw new UnresolvableSourceException($source);
        }

        $targetAuthorizable = $this->resolver->fromSubject($target);
        if (!isset($targetAuthorizable)) {
            throw new UnresolvableTargetException($target);
        }

        $grant = new Grant($sourceAuthorizable, $targetAuthorizable, $permission);

        $this->permissionRepository->revoke($grant);
    }

    final public function getRepository(): PermissionRepository
    {
        return $this->permissionRepository;
    }



}