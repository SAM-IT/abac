<?php
declare(strict_types=1);

namespace test\repositories;

use SamIT\abac\interfaces\PermissionRepository;
use SamIT\abac\repositories\MemoryRepository;

/**
 * * @covers \SamIT\abac\repositories\MemoryRepository
 */
class MemoryRepositoryTest extends PermissionRepositoryTest
{

    protected function getRepository(): PermissionRepository
    {
        return new MemoryRepository();
    }
}
