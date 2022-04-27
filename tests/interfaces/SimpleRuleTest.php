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

    /**
     * @return iterable<array{0: Authorizable, 1: Authorizable, 2: string, 3: Environment, 4: AccessChecker, 5: bool}>
     */
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
    ): void {
        $rule = $this->getRule();
        static::assertSame($result, $rule->execute($source, $target, $permission, $environment, $accessChecker));
    }

    public function testHasDescription(): void
    {
        static::assertNotEmpty($this->getRule()->getDescription());
    }
}
