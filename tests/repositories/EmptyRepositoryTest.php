<?php
declare(strict_types=1);

namespace test;

use SamIT\abac\interfaces\PermissionRepository;
use SamIT\abac\repositories\EmptyRepository;

/**
 * @covers \SamIT\abac\repositories\EmptyRepository
 */
class EmptyRepositoryTest extends PermissionRepositoryTest
{

    protected function getRepository(): PermissionRepository
    {
        return new EmptyRepository();
    }
}