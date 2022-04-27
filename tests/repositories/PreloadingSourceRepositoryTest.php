<?php

declare(strict_types=1);

namespace test\repositories;

use SamIT\abac\repositories\MemoryRepository;
use SamIT\abac\repositories\PreloadingSourceRepository;
use SamIT\abac\values\Authorizable;
use SamIT\abac\values\Grant;

/**
 * @covers \SamIT\abac\repositories\PreloadingSourceRepository
 */
class PreloadingSourceRepositoryTest extends PermissionRepositoryTest
{
    protected function getRepository(): PreloadingSourceRepository
    {
        return new PreloadingSourceRepository(new MemoryRepository());
    }


    public function testPreloadedCache(): void
    {
        $base = new MemoryRepository();
        $subject = new PreloadingSourceRepository($base);

        $source = new Authorizable('a', 'b');
        $target = new Authorizable('c', 'd');
        $permission = 'e';

        $grant = new Grant($source, $target, $permission);

        $subject->preloadSource($source);
        $base->grant($grant);
        self::assertFalse($subject->check($grant));

        $subject->preloadSource($source);
        $base->revoke($grant);
        self::assertTrue($subject->check($grant));
    }
}
