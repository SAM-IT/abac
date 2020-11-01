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
class EmptyRepositoryTest extends PermissionRepositoryTest
{

    protected function getRepository(): PermissionRepository
    {
        return new EmptyRepository();
    }

    public function testGrant()
    {
        $this->markTestSkipped('Empty repository does not support granting');
    }

    public function testGrantThrowsException()
    {
        $this->expectException(\RuntimeException::class);
        parent::testGrant();
    }
}
