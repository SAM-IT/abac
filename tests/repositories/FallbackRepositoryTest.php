<?php
declare(strict_types=1);

namespace test\repositories;

use SamIT\abac\interfaces\PermissionRepository;
use SamIT\abac\repositories\EmptyRepository;
use SamIT\abac\repositories\FallbackRepository;
use SamIT\abac\repositories\MemoryRepository;

/**
 * * @covers \SamIT\abac\repositories\FallbackRepository
 */
class FallbackRepositoryTest extends PermissionRepositoryTest
{

    protected function getRepository(): PermissionRepository
    {
        return new FallbackRepository(new MemoryRepository(), new EmptyRepository());
    }
}