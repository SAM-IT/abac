<?php

declare(strict_types=1);

namespace SamIT\abac\rules;

use Closure;
use SamIT\abac\interfaces\AccessChecker;
use SamIT\abac\interfaces\Environment;
use SamIT\abac\interfaces\SimpleRule;
use function PHPStan\dumpType;

final class AdminRole implements SimpleRule
{
    /**
     * @var array<string, true>
     */
    private array $admins = [];

    /**
     * @var Closure(object): string
     */
    private Closure $serializer;
    /**
     * AdminRole constructor.
     * @param list<object|string> $admins
     * @param callable(object): string $serializer
     */
    public function __construct(array $admins, callable $serializer)
    {
        $this->serializer = Closure::fromCallable($serializer);
        foreach ($admins as $admin) {
            $this->admins[is_object($admin) ? ($this->serializer)($admin) : $admin] = true;
        }
    }

    public function getDescription(): string
    {
        return "you are an admin";
    }

    public function execute(
        object $source,
        object $target,
        string $permission,
        Environment $environment,
        AccessChecker $accessChecker
    ): bool {
        $key = ($this->serializer)($source);
        return isset($this->admins[$key]);
    }
}
