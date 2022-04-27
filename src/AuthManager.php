<?php

declare(strict_types=1);

namespace SamIT\abac;

use SamIT\abac\exceptions\NestingException;
use SamIT\abac\exceptions\UnresolvableException;
use SamIT\abac\exceptions\UnresolvableSourceException;
use SamIT\abac\exceptions\UnresolvableTargetException;
use SamIT\abac\interfaces\AccessChecker;
use SamIT\abac\interfaces\Authorizable;
use SamIT\abac\interfaces\Environment;
use SamIT\abac\interfaces\PermissionRepository;
use SamIT\abac\interfaces\Resolver;
use SamIT\abac\interfaces\RuleEngine;
use SamIT\abac\values\Grant;

class AuthManager implements AccessChecker
{
    public const MAX_DEPTH = 400;

    private int $depth = 0;
    /** @var array<string, bool>  */
    private array $partialResults = [];

    public function __construct(
        private readonly RuleEngine $ruleEngine,
        private readonly PermissionRepository $permissionRepository,
        private readonly Resolver $resolver,
        private readonly Environment $environment
    ) {
    }

    private function storePartial(Grant $grant, bool $result): void
    {
        if ($this->depth > 1) {
            $source = $grant->getSource();
            $target = $grant->getTarget();
            $key = "{$source->getAuthName()}|{$source->getId()}|{$target->getAuthName()}|{$target->getId()}|{$grant->getPermission()}";
            $this->partialResults[$key] = $result;
        }
    }

    /**
     * Checks the partial result cache
     * @param Grant $grant
     * @return bool|null
     */
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


            try {
                $sourceAuthorizable = $this->resolver->fromSubject($source);
                $targetAuthorizable = $this->resolver->fromSubject($target);
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
            } catch (UnresolvableException $e) {
                // If either source or target is not resolvable, we can still use the rule engine.
                return $this->ruleEngine->check(
                    $source,
                    $target,
                    $permission,
                    $this->environment,
                    $this
                );
            }
        } finally {
            $this->depth--;
            if ($this->depth === 0) {
                $this->partialResults = [];
            }
        }
    }

    public function grant(object $source, object $target, string $permission): void
    {
        try {
            $sourceAuthorizable = $this->resolver->fromSubject($source);
        } catch (UnresolvableException) {
            throw UnresolvableSourceException::forSubject($source);
        }

        try {
            $targetAuthorizable = $this->resolver->fromSubject($target);
        } catch (UnresolvableException) {
            throw UnresolvableTargetException::forSubject($target);
        }

        $grant = new Grant($sourceAuthorizable, $targetAuthorizable, $permission);

        $this->permissionRepository->grant($grant);
    }

    public function revoke(object $source, object $target, string $permission): void
    {
        try {
            $sourceAuthorizable = $this->resolver->fromSubject($source);
        } catch (UnresolvableException) {
            throw UnresolvableSourceException::forSubject($source);
        }

        try {
            $targetAuthorizable = $this->resolver->fromSubject($target);
        } catch (UnresolvableException) {
            throw UnresolvableTargetException::forSubject($target);
        }

        $grant = new Grant($sourceAuthorizable, $targetAuthorizable, $permission);

        $this->permissionRepository->revoke($grant);
    }

    final public function getRepository(): PermissionRepository
    {
        return $this->permissionRepository;
    }

    final public function resolveSubject(object $subject): Authorizable
    {
        return $this->resolver->fromSubject($subject);
    }
}
