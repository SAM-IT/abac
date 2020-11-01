<?php
declare(strict_types=1);

namespace test\repositories;

/**
 * @covers \SamIT\abac\repositories\CachedReadRepository
 */
class CachedReadRepositoryTest extends PermissionRepositoryTest
{

    protected function getRepository(): \SamIT\abac\interfaces\PermissionRepository
    {
        return new \SamIT\abac\repositories\CachedReadRepository(new \SamIT\abac\repositories\MemoryRepository());
    }
}
