<?php
declare(strict_types=1);

namespace test;

use PHPUnit\Framework\TestCase;
use SamIT\abac\AuthManager;
use SamIT\abac\engines\SimpleEngine;
use SamIT\abac\exceptions\NestingException;
use SamIT\abac\interfaces\AccessChecker;
use SamIT\abac\interfaces\Authorizable;
use SamIT\abac\interfaces\Environment;
use SamIT\abac\interfaces\Resolver;
use SamIT\abac\interfaces\RuleEngine;
use SamIT\abac\interfaces\SimpleRule;
use SamIT\abac\repositories\EmptyRepository;

/**
 * Class AuthManagerTest
 * @package test
 */
final class AuthManagerTest extends TestCase
{
    /**
     * @covers \SamIT\abac\AuthManager
     */
    public function testDepth()
    {
        // Infinite loop rule engine
        $ruleEngine = new class implements RuleEngine {

            public function check(
                object $source,
                object $target,
                string $permission,
                Environment $environment,
                AccessChecker $accessChecker
            ): bool {
                return $accessChecker->check($source, $target, $permission);
            }
        };
        $repo = new EmptyRepository();

        $resolver = new class implements Resolver {

            public function fromSubject(object $object): ?Authorizable
            {
                return $object instanceof Authorizable ? $object : null;
            }

            public function toSubject(Authorizable $authorizable): ?object
            {
                return $authorizable;
            }
        };

        $env = new class extends \ArrayObject implements Environment {};

        $manager = new AuthManager($ruleEngine, $repo, $resolver, $env);
        $this->expectException(NestingException::class);
        $source = $target = new class implements Authorizable {
            public function getId(): string {
                return '';
            }

            public function getAuthName(): string
            {
                return '';
            }
        };

        $manager->check($source, $target, 'test');
    }

    /**
     * @covers \SamIT\abac\AuthManager
     */
    public function testPartialCaching()
    {
        $rule = new class implements SimpleRule {
            public $counter = 0;
            public function getDescription(): string { return ''; }
            public function execute(
                object $source,
                object $target,
                string $permission,
                Environment $environment,
                AccessChecker $accessChecker
            ): bool {
                if ($permission === 'a') {
                    $this->counter++;
                    return true;
                } else {
                    return false;
                }

            }
        };

        $engine = new SimpleEngine([
            $rule,
            new class implements SimpleRule {
                public function getDescription(): string { return ''; }
                public function execute(
                    object $source,
                    object $target,
                    string $permission,
                    Environment $environment,
                    AccessChecker $accessChecker
                ): bool {
                    return $permission === 'b'
                        && $accessChecker->check($source, $target, 'a')
                        && $accessChecker->check($source, $target, 'c');
                }
            },
            new class implements SimpleRule {
                public function getDescription(): string { return ''; }
                public function execute(
                    object $source,
                    object $target,
                    string $permission,
                    Environment $environment,
                    AccessChecker $accessChecker
                ): bool {
                    return $permission === 'c'
                        && $accessChecker->check($source, $target, 'a');
                }
            }
        ]);

        $repo = new EmptyRepository();

        $resolver = new class implements Resolver {

            public function fromSubject(object $object): ?Authorizable
            {
                return $object instanceof Authorizable ? $object : null;
            }

            public function toSubject(Authorizable $authorizable): ?object
            {
                return $authorizable;
            }
        };

        $env = new class extends \ArrayObject implements Environment {};

        $manager = new AuthManager($engine, $repo, $resolver, $env);
        $source = $target = new class implements Authorizable {
            public function getId(): string {
                return '';
            }

            public function getAuthName(): string
            {
                return '';
            }
        };

        $this->assertTrue($manager->check($source, $target, 'b'));
        $this->assertSame(1, $rule->counter);
    }

    public function testCheckUnresolvableSourceException()
    {
        $engine = new SimpleEngine([]);
        $repo = new EmptyRepository();

        $resolver = new class implements Resolver {
            public function fromSubject(object $object): ?Authorizable
            {
                return $object instanceof Authorizable ? $object : null;
            }

            public function toSubject(Authorizable $authorizable): ?object
            {
                return $authorizable;
            }
        };

        $env = new class extends \ArrayObject implements Environment {};

        $manager = new AuthManager($engine, $repo, $resolver, $env);

        $this->expectException(\RuntimeException::class);
        $manager->check(new \stdClass(), $repo, 'doSomethingCool');
    }

    public function testCheckUnresolvableTargetException()
    {
        $engine = new SimpleEngine([]);
        $repo = new EmptyRepository();

        $resolver = new class implements Resolver {
            public function fromSubject(object $object): ?Authorizable
            {
                return $object instanceof Authorizable ? $object : null;
            }

            public function toSubject(Authorizable $authorizable): ?object
            {
                return $authorizable;
            }
        };

        $env = new class extends \ArrayObject implements Environment {};

        $manager = new AuthManager($engine, $repo, $resolver, $env);

        $this->expectException(\RuntimeException::class);
        $manager->check(new \SamIT\abac\values\Authorizable('13', 'test'), new \stdClass(), 'doSomethingCool');
    }
}