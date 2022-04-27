<?php

declare(strict_types=1);

namespace test\repositories;

use SamIT\abac\interfaces\PermissionRepository;
use SamIT\abac\repositories\EmptyRepository;
use SamIT\abac\values\Authorizable;
use SamIT\abac\values\Grant;

/**
 * @covers \SamIT\abac\repositories\EmptyRepository
 */
final class EmptyRepositoryTest extends PermissionRepositoryTest
{
    protected function getRepository(): PermissionRepository
    {
        return new EmptyRepository();
    }

    public function testGrant(): void
    {
        self::markTestSkipped("Granting not supported");
    }

    public function testRevoke(): void
    {
        self::markTestSkipped("Revoking not supported");
    }
}
