<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use SamIT\abac\AuthManager;
use SamIT\abac\interfaces\AccessChecker;
use SamIT\abac\interfaces\Authorizable;
use SamIT\abac\interfaces\Environment;
use SamIT\abac\interfaces\Resolver;
use SamIT\abac\interfaces\RuleEngine;

final class AuthManagerTest extends TestCase
{

    /**
     * @covers \SamIT\abac\AuthManager
     */
    public function testDepth()
    {
        $ruleEngine = new class implements RuleEngine {

            public function check(
                Authorizable $source,
                Authorizable $target,
                string $permission,
                Environment $environment,
                AccessChecker $accessChecker
            ): bool {
                $accessChecker->check($source, $target, $permission);
            }
        };
        $repo = new \SamIT\abac\repositories\EmptyRepository();

        $resolver = new class implements Resolver {

            public function fromSubject(object $object): ?Authorizable
            {
                return null;
            }

            public function toSubject(Authorizable $authorizable): ?object
            {
                return null;
            }
        };

        $env = new class extends ArrayObject implements Environment {};

        $manager = new AuthManager($ruleEngine, $repo, $resolver, $env);
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Max nesting depth exceeded');
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
}