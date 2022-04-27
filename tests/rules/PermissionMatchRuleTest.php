<?php

declare(strict_types=1);

namespace test\rules;

use SamIT\abac\interfaces\AccessChecker;
use SamIT\abac\interfaces\Environment;
use SamIT\abac\rules\PermissionMatchRule;
use SamIT\abac\values\Authorizable;
use test\interfaces\SimpleRuleTest;

/**
 * @covers \SamIT\abac\rules\PermissionMatchRule
 */
final class PermissionMatchRuleTest extends SimpleRuleTest
{
    /**
     * @param list<string> $sourceNames
     * @param list<string> $targetNames
     */
    protected function getRule(array $sourceNames = [], array $targetNames = []): PermissionMatchRule
    {
        return new PermissionMatchRule('/^t[1-5]est$/', $sourceNames, $targetNames);
    }

    public function checkProvider(): iterable
    {
        $source = new Authorizable('id1', 'name');
        $admin = new Authorizable('1', 'admin');
        $target = new Authorizable('id2', 'name');
        $environment = new class() extends \ArrayObject implements Environment {
        };
        $accessChecker = $this->getMockBuilder(AccessChecker::class)->getMock();
        $accessChecker->expects(self::never())->method('check');

        yield [$source, $target, 't1est', $environment, $accessChecker, true];
        yield [$admin, $target, 't2est', $environment, $accessChecker, true];
        yield [$source, $admin, 't3est', $environment, $accessChecker, true];
        yield [$source, $target, 't4est', $environment, $accessChecker, true];
        yield [$admin, $target, 't5est', $environment, $accessChecker, true];
        yield [$source, $admin, 't6est', $environment, $accessChecker, false];
        yield [$source, $admin, 'abc', $environment, $accessChecker, false];
    }

    public function testGetPermissions(): void
    {
        self::assertEmpty($this->getRule()->getPermissions());
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
