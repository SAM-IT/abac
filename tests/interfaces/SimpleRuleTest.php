<?php
declare(strict_types=1);

namespace test\interfaces;


use PHPUnit\Framework\TestCase;
use SamIT\abac\interfaces\AccessChecker;
use SamIT\abac\interfaces\Authorizable;
use SamIT\abac\interfaces\Environment;
use SamIT\abac\interfaces\SimpleRule;

abstract class SimpleRuleTest extends TestCase
{

    abstract protected function getRule(): SimpleRule;

    abstract public function checkProvider(): iterable;

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
        $rule = $this->getRule();

        $this->assertSame($result, $rule->execute($source, $target, $permission, $environment, $accessChecker));
    }
}