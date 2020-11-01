<?php
declare(strict_types=1);

namespace test\repositories;

use SamIT\abac\repositories\MemoryRepository;
use SamIT\abac\repositories\PreloadingSourceRepository;

/**
 * @covers \SamIT\abac\repositories\PreloadingSourceRepository
 */
class PreloadingSourceRepositoryTest extends PermissionRepositoryTest
{

    protected function getRepository(): \SamIT\abac\interfaces\PermissionRepository
    {
        return new \SamIT\abac\repositories\PreloadingSourceRepository(new \SamIT\abac\repositories\MemoryRepository());
    }


    public function testPreloadedCache()
    {
        $base = new MemoryRepository();
        $subject = new PreloadingSourceRepository($base);

        $source = new \SamIT\abac\values\Authorizable('a', 'b');
        $target = new \SamIT\abac\values\Authorizable( 'c', 'd');
        $permission = 'e';

        $grant = new \SamIT\abac\values\Grant($source, $target, $permission);

        $subject->preloadSource($source);
        $base->grant($grant);
        $this->assertFalse($subject->check($grant));

        $subject->preloadSource($source);
        $base->revoke($grant);
        $this->assertTrue($subject->check($grant));

    }
}