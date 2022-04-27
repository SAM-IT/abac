<?php

declare(strict_types=1);

namespace test;

use PHPUnit\Framework\TestCase;
use SamIT\abac\AuthManager;
use SamIT\abac\engines\SimpleEngine;
use SamIT\abac\exceptions\NestingException;
use SamIT\abac\exceptions\UnresolvableSourceException;
use SamIT\abac\exceptions\UnresolvableTargetException;
use SamIT\abac\interfaces\AccessChecker;
use SamIT\abac\interfaces\Authorizable;
use SamIT\abac\interfaces\Environment;
use SamIT\abac\interfaces\PermissionRepository;
use SamIT\abac\interfaces\Resolver;
use SamIT\abac\interfaces\RuleEngine;
use SamIT\abac\interfaces\SimpleRule;
use SamIT\abac\repositories\EmptyRepository;
use SamIT\abac\resolvers\AuthorizableResolver;
use SamIT\abac\rules\ImpliedPermission;
use SamIT\abac\values\Authorizable as AuthorizableValue;
use stdClass;

/**
 * @covers \SamIT\abac\AuthManager
 */
final class AuthManagerTest extends TestCase
{
    public function testDepth(): void
    {
        // Infinite loop rule engine
        $ruleEngine = new class() implements RuleEngine {
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

        $resolver = new AuthorizableResolver();

        $env = new class() extends \ArrayObject implements Environment {
        };

        $manager = new AuthManager($ruleEngine, $repo, $resolver, $env);
        $this->expectException(NestingException::class);
        $source = $target = new class() implements Authorizable {
            public function getId(): string
            {
                return '';
            }

            public function getAuthName(): string
            {
                return '';
            }
        };

        $manager->check($source, $target, 'test');
    }

    public function testPartialCaching(): void
    {
        $rule = new class() implements SimpleRule {
            public int $counter = 0;
            public function getDescription(): string
            {
                return 'rule a';
            }
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

        $engine = new SimpleEngine(
            $rule,
            new class() implements SimpleRule {
                public function getDescription(): string
                {
                    return 'rule b';
                }
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
            new ImpliedPermission('a', ['c'])
        );

        $repo = new EmptyRepository();

        $resolver = new AuthorizableResolver();

        $env = new class() extends \ArrayObject implements Environment {
        };

        $manager = new AuthManager($engine, $repo, $resolver, $env);
        $source = $target = new AuthorizableValue("", "");

        self::assertTrue($manager->check($source, $target, 'b'));
        self::assertSame(1, $rule->counter);
    }

    public function testCheckUnresolvableSourceException(): void
    {
        $engine = new SimpleEngine();
        $repo = new EmptyRepository();

        $resolver = new AuthorizableResolver();

        $env = new class() extends \ArrayObject implements Environment {
        };

        $manager = new AuthManager($engine, $repo, $resolver, $env);

        self::assertFalse($manager->check(new stdClass(), $repo, 'doSomethingCool'));
    }

    public function testCheckUnresolvableTargetException(): void
    {
        $engine = new SimpleEngine();
        $repo = new EmptyRepository();

        $resolver = new AuthorizableResolver();

        $env = new class() extends \ArrayObject implements Environment {
        };

        $manager = new AuthManager($engine, $repo, $resolver, $env);

        self::assertFalse($manager->check(new AuthorizableValue('13', 'test'), new stdClass(), 'doSomethingCool'));
    }

    public function testGetRepository(): void
    {
        $repo = new EmptyRepository();
        $env = new class() extends \ArrayObject implements Environment {
        };
        $manager = new AuthManager(new SimpleEngine(), $repo, new AuthorizableResolver(), $env);

        self::assertSame($repo, $manager->getRepository());
        self::assertNotSame(clone $repo, $manager->getRepository());
    }

    public function testGrantForwardToRepository(): void
    {
        $repo = $this->getMockBuilder(PermissionRepository::class)->getMock();
        $repo->expects(self::once())->method('grant');

        $env = new class() extends \ArrayObject implements Environment {
        };
        $manager = new AuthManager(new SimpleEngine(), $repo, new AuthorizableResolver(), $env);

        $authorizable = new AuthorizableValue('a', 'b');
        $manager->grant($authorizable, $authorizable, 'test');
    }

    public function testGrantUnresolvableSource(): void
    {
        $repo = $this->getMockBuilder(PermissionRepository::class)->getMock();
        $repo->expects(self::never())->method('grant');

        $env = new class() extends \ArrayObject implements Environment {
        };
        $manager = new AuthManager(new SimpleEngine(), $repo, new AuthorizableResolver(), $env);

        $authorizable = new AuthorizableValue('a', 'b');
        $this->expectException(UnresolvableSourceException::class);
        $manager->grant(new stdClass(), $authorizable, 'test');
    }

    public function testGrantUnresolvableTarget(): void
    {
        $repo = $this->getMockBuilder(PermissionRepository::class)->getMock();
        $repo->expects(self::never())->method('grant');

        $env = new class() extends \ArrayObject implements Environment {
        };
        $manager = new AuthManager(new SimpleEngine(), $repo, new AuthorizableResolver(), $env);

        $authorizable = new AuthorizableValue('a', 'b');
        $this->expectException(UnresolvableTargetException::class);
        $manager->grant($authorizable, new stdClass(), 'test');
    }

    public function testRevokeForwardToRepository(): void
    {
        $repo = $this->getMockBuilder(PermissionRepository::class)->getMock();
        $repo->expects(self::once())->method('revoke');

        $env = new class() extends \ArrayObject implements Environment {
        };
        $manager = new AuthManager(new SimpleEngine(), $repo, new AuthorizableResolver(), $env);

        $authorizable = new AuthorizableValue('a', 'b');
        $manager->revoke($authorizable, $authorizable, 'test');
    }

    public function testRevokeUnresolvableSource(): void
    {
        $repo = $this->getMockBuilder(PermissionRepository::class)->getMock();
        $repo->expects(self::never())->method('grant');

        $env = new class() extends \ArrayObject implements Environment {
        };
        $manager = new AuthManager(new SimpleEngine(), $repo, new AuthorizableResolver(), $env);

        $authorizable = new AuthorizableValue('a', 'b');
        $this->expectException(UnresolvableSourceException::class);
        $manager->revoke(new stdClass(), $authorizable, 'test');
    }

    public function testRevokeUnresolvableTarget(): void
    {
        $repo = $this->getMockBuilder(PermissionRepository::class)->getMock();
        $repo->expects(self::never())->method('grant');

        $env = new class() extends \ArrayObject implements Environment {
        };
        $manager = new AuthManager(new SimpleEngine(), $repo, new AuthorizableResolver(), $env);

        $authorizable = new AuthorizableValue('a', 'b');
        $this->expectException(UnresolvableTargetException::class);
        $manager->revoke($authorizable, new stdClass(), 'test');
    }

    public function testResolveSubjectForwardsToResolver(): void
    {
        $resolver = $this->getMockBuilder(Resolver::class)->getMock();
        $resolver->expects(self::once())->method('fromSubject');
        $env = new class() extends \ArrayObject implements Environment {
        };
        $manager = new AuthManager(new SimpleEngine(), new EmptyRepository(), $resolver, $env);

        $manager->resolveSubject(new stdClass());
    }
}
