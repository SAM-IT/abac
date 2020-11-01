<?php
declare(strict_types=1);

namespace test\repositories;

use PHPUnit\Framework\TestCase;
use SamIT\abac\interfaces\PermissionRepository;
use function iter\toArray;

abstract class PermissionRepositoryTest extends TestCase
{


    abstract protected function getRepository(): PermissionRepository;

    public function testGrant()
    {
        $repo = $this->getRepository();

        $source = new \SamIT\abac\values\Authorizable('a', 'b');
        $target = new \SamIT\abac\values\Authorizable('c', 'd');
        $permission = 'e';

        $grant = new \SamIT\abac\values\Grant($source, $target, $permission);

        $this->assertFalse($repo->check($grant));

        // Either grant succeeds and check must return TRUE
        $repo->grant($grant);
        $this->assertTrue($repo->check($grant));
        $this->assertTrue($repo->check(clone $grant));
    }



    public function testRevokeUngranted()
    {
        $repo = $this->getRepository();

        $source = new \SamIT\abac\values\Authorizable('a', 'b');
        $target = new \SamIT\abac\values\Authorizable('c', 'd');
        $permission = 'e';

        $grant = new \SamIT\abac\values\Grant($source, $target, $permission);

        $repo->revoke($grant);
        $this->assertFalse($repo->check($grant));
    }

    public function testEmptySearch()
    {
        $repo = $this->getRepository();
        $this->assertEmpty(toArray($repo->search(null, null, null)));
    }

    /**
     * @depends testRevoke
     */
    public function testSearch()
    {
        $repo = $this->getRepository();

        $source = new \SamIT\abac\values\Authorizable('a', 'b');
        $target = new \SamIT\abac\values\Authorizable('c', 'd');
        $permission = 'e';

        $grant1 = new \SamIT\abac\values\Grant($source, $target, $permission);

        $repo->grant($grant1);
        $this->assertCount(1, toArray($repo->search(null, null, null)));
        $this->assertCount(1, toArray($repo->search($source, $target, $permission)));

        $this->assertCount(0, toArray($repo->search($source, $target, 'f')));

        $this->assertCount(0, toArray($repo->search($source, $source, $permission)));
        $this->assertCount(0, toArray($repo->search($target, $target, $permission)));
    }

    /**
     * @depends testGrant
     */
    public function testRevoke()
    {
        $repo = $this->getRepository();

        $source = new \SamIT\abac\values\Authorizable('a', 'b');
        $target = new \SamIT\abac\values\Authorizable('c', 'd');
        $permission = 'e';

        $grant = new \SamIT\abac\values\Grant($source, $target, $permission);

        $repo->grant($grant);
        $this->assertTrue($repo->check($grant));

        try {
            $repo->revoke($grant);
            $this->assertFalse($repo->check($grant));
        } catch (\RuntimeException $e) {
            $this->assertTrue($repo->check($grant));
        }
    }
}
