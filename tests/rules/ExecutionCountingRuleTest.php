<?php

declare(strict_types=1);

namespace test\rules;

use SamIT\abac\interfaces\AccessChecker;
use SamIT\abac\interfaces\Environment;
use SamIT\abac\interfaces\Rule;
use SamIT\abac\interfaces\SimpleRule;
use SamIT\abac\rules\ExecutionCountingRule;
use SamIT\abac\values\Authorizable;
use test\interfaces\SimpleRuleTest;

/**
 * @covers \SamIT\abac\rules\ExecutionCountingRule
 */
final class ExecutionCountingRuleTest extends SimpleRuleTest
{
    /**
     * @param list<string> $sourceNames
     * @param list<string> $targetNames
     */
    protected function getRule(array $sourceNames = [], array $targetNames = []): ExecutionCountingRule
    {
        $rule = $this->getMockBuilder(Rule::class)->getMock();

        $rule->expects(self::any())->method('getDescription')->willReturn('test');
        $rule->expects(self::any())->method('getSourceNames')->willReturn($sourceNames);
        $rule->expects(self::any())->method('getTargetNames')->willReturn($targetNames);
        $rule->expects(self::any())->method('execute')->willReturn(false);
        return new ExecutionCountingRule($rule);
    }

    /**
     * @dataProvider checkProvider
     */
    public function testCounter(
        \SamIT\abac\interfaces\Authorizable $source,
        Authorizable $target,
        string $permission,
        Environment $environment,
        AccessChecker $accessChecker,
        bool $result
    ): void {
        $rule = $this->getRule();

        $count = random_int(1, 10);
        for ($i = 0; $i < $count; $i++) {
            self::assertSame($result, $rule->execute($source, $target, $permission, $environment, $accessChecker));
        }
        self::assertSame($count, $rule->getExecutions());
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

        yield [$source, $target, 't1est', $environment, $accessChecker, false];
        yield [$admin, $target, 't2est', $environment, $accessChecker, false];
        yield [$source, $admin, 't3est', $environment, $accessChecker, false];
        yield [$source, $target, 't4est', $environment, $accessChecker, false];
        yield [$admin, $target, 't5est', $environment, $accessChecker, false];
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
