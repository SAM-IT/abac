<?php

declare(strict_types=1);

namespace test\rules;

use SamIT\abac\helpers\AuthorizableSerializer;
use SamIT\abac\interfaces\AccessChecker;
use SamIT\abac\interfaces\Environment;
use SamIT\abac\interfaces\SimpleRule;
use SamIT\abac\rules\AdminRole;
use SamIT\abac\values\Authorizable;
use test\interfaces\SimpleRuleTest;

/**
 * @covers \SamIT\abac\rules\AdminRole
 */
final class AdminRoleTest extends SimpleRuleTest
{
    protected function getRule(): SimpleRule
    {
        return new AdminRole([
            new Authorizable('1', 'admin')
            /** @phpstan-ignore-next-line  */
        ], new AuthorizableSerializer());
    }

    public function checkProvider(): iterable
    {
        $source = new Authorizable('id1', 'name');
        $admin = new Authorizable('1', 'admin');
        $target = new Authorizable('id2', 'name');
        $permission = 'test';
        $environment = new class() extends \ArrayObject implements Environment {
        };
        $accessChecker = new class() implements AccessChecker {
            public function check(object $source, object $target, string $permission): bool
            {
                return false;
            }
        };

        yield [$source, $target, $permission, $environment, $accessChecker, false];
        yield [$admin, $target, $permission, $environment, $accessChecker, true];
        yield [$source, $admin, $permission, $environment, $accessChecker, false];
    }
}
