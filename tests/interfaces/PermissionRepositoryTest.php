<?php

declare(strict_types=1);

namespace test\repositories;

use PHPUnit\Framework\TestCase;
use SamIT\abac\interfaces\PermissionRepository;
use SamIT\abac\values\Authorizable;
use SamIT\abac\values\Grant;
use function iter\toArray;

abstract class PermissionRepositoryTest extends TestCase
{
    abstract protected function getRepository(): PermissionRepository;

    public function testGrant(): void
    {
        $repo = $this->getRepository();

        $source = new Authorizable('a', 'b');
        $target = new Authorizable('c', 'd');
        $permission = 'e';

        $grant = new Grant($source, $target, $permission);

        self::assertFalse($repo->check($grant));

        // Either grant succeeds and check must return TRUE
        $repo->grant($grant);
        self::assertTrue($repo->check($grant));
        self::assertTrue($repo->check(clone $grant));
    }



    public function testRevokeUngranted(): void
    {
        $repo = $this->getRepository();

        $source = new Authorizable('a', 'b');
        $target = new Authorizable('c', 'd');
        $permission = 'e';

        $grant = new Grant($source, $target, $permission);

        $repo->revoke($grant);
        static::assertFalse($repo->check($grant));
    }

    public function testEmptySearch(): void
    {
        $repo = $this->getRepository();
        static::assertEmpty(toArray($repo->search(null, null, null)));
    }

    /**
     * @depends testRevoke
     */
    public function testSearch(): void
    {
        $repo = $this->getRepository();

        $source = new Authorizable('a', 'b');
        $target = new Authorizable('c', 'd');
        $permission = 'e';

        $grant1 = new Grant($source, $target, $permission);

        $repo->grant($grant1);
        static::assertCount(1, toArray($repo->search(null, null, null)));
        static::assertCount(1, toArray($repo->search($source, $target, $permission)));

        static::assertCount(0, toArray($repo->search($source, $target, 'f')));

        static::assertCount(0, toArray($repo->search($source, $source, $permission)));
        static::assertCount(0, toArray($repo->search($target, $target, $permission)));
    }

    /**
     * @depends testGrant
     */
    public function testRevoke(): void
    {
        $repo = $this->getRepository();

        $source = new Authorizable('a', 'b');
        $target = new Authorizable('c', 'd');
        $permission = 'e';

        $grant = new Grant($source, $target, $permission);

        $repo->grant($grant);
        static::assertTrue($repo->check($grant));

        try {
            $repo->revoke($grant);
            static::assertFalse($repo->check($grant));
        } catch (\RuntimeException $e) {
            static::assertTrue($repo->check($grant));
        }
    }
}
