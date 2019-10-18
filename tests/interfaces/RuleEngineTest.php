<?php
declare(strict_types=1);

namespace test;

use PHPUnit\Framework\TestCase;
use SamIT\abac\Authorizable;
use SamIT\abac\interfaces\AccessChecker;
use SamIT\abac\interfaces\Environment;
use SamIT\abac\interfaces\RuleEngine;
use SamIT\abac\interfaces\SimpleRule;
use SamIT\abac\rules\ExecutionCountingRule;
use SamIT\abac\rules\PermissionMatchRule;

abstract class RuleEngineTest extends TestCase
{

    /**
     * @param SimpleRule[] $rules
     * @return RuleEngine
     */
    abstract protected function getEngine(iterable $rules): RuleEngine;


    final public function checkProvider(): iterable
    {
        $source = new Authorizable('id1', 'name');
        $target = new Authorizable('id2', 'name');
        $environment = new class extends \ArrayObject implements Environment {};
        $accessChecker = new class implements AccessChecker {
            public function check(object $source, object $target, string $permission): bool
            {
                return false;
            }
        };
        yield [$source, $target, 'abc', $environment, $accessChecker, true];
        yield [$source, $target, 'def', $environment, $accessChecker, true];
        yield [$source, $target, 'ghi', $environment, $accessChecker, true];
        yield [$source, $target, 'jkl', $environment, $accessChecker, false];
    }

    protected function getRules(): iterable
    {
        return [
            new ExecutionCountingRule(new PermissionMatchRule('/^abc$/')),
            new ExecutionCountingRule(new PermissionMatchRule('/^def$/')),
            new ExecutionCountingRule(new PermissionMatchRule('/^ghi$/'))
        ];
    }

    /**
     * @dataProvider checkProvider
     */
    public function testCheck(
        Authorizable $source,
        Authorizable $target,
        string $permission,
        Environment $environment,
        AccessChecker $accessChecker,
        bool $result
    ) {

        $rules = $this->getRules();
        $engine = $this->getEngine($rules);
        $this->assertSame($result, $engine->check($source, $target, $permission, $environment, $accessChecker));

        // In case access is denied, all rules must have been checked.
        if (!$result) {
            /** @var ExecutionCountingRule $rule */
            foreach ($rules as $rule) {
                $this->assertSame(1, $rule->getExecutions());
            }
        }
    }
}