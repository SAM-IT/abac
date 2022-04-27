<?php

declare(strict_types=1);

namespace test\rules;

use SamIT\abac\interfaces\AccessChecker;
use SamIT\abac\interfaces\Environment;
use SamIT\abac\interfaces\SimpleRule;
use SamIT\abac\rules\ImpliedPermission;
use SamIT\abac\values\Authorizable;
use test\interfaces\SimpleRuleTest;

/**
 * Class ImpliedPermissionTest
 * @package test\rules
 * @covers \SamIT\abac\rules\ImpliedPermission
 */
class ImpliedPermissionTest extends SimpleRuleTest
{
    /**
     * @param list<string> $sourceNames
     * @param list<string> $targetNames
     */
    protected function getRule(array $sourceNames = [], array $targetNames = []): ImpliedPermission
    {
        return new ImpliedPermission('a', ['b'], $sourceNames, $targetNames);
    }

    public function checkProvider(): iterable
    {
        $source = new Authorizable('id1', 'name');
        $admin = new Authorizable('1', 'admin');
        $target = new Authorizable('id2', 'name');
        $environment = new class() extends \ArrayObject implements Environment {
        };
        $accessChecker = new class() implements AccessChecker {
            public function check(object $source, object $target, string $permission): bool
            {
                if ($permission === 'a'
                    && $source instanceof Authorizable
                    && $source->getId() === '1'
                ) {
                    return true;
                }
                return false;
            }
        };

        yield [$source, $target, 'a', $environment, $accessChecker, false];
        // The rule does not allow 'a', the access checker does.
        yield [$admin, $target, 'a', $environment, $accessChecker, false];
        // The rule does allow 'b', if the access checker allows a.
        yield [$admin, $target, 'b', $environment, $accessChecker, true];
        yield [$source, $admin, 'a', $environment, $accessChecker, false];
        yield [$source, $admin, 'a', $environment, $accessChecker, false];
    }

    public function testGetPermissions(): void
    {
        self::assertSame(['b'], $this->getRule()->getPermissions());
    }

    public function testGetSourceNames(): void
    {
        $sourceNames = [];
        for ($i = 0; $i < 10; $i++) {
            $sourceNames[] = random_bytes(15);
        }
        self::assertSame($sourceNames, $this->getRule($sourceNames)->getSourceNames());
    }

    public function testGetTargetNames(): void
    {
        $targetNames = [];
        for ($i = 0; $i < 10; $i++) {
            $targetNames[] = random_bytes(15);
        }
        self::assertSame($targetNames, $this->getRule([], $targetNames)->getTargetNames());
    }
}
