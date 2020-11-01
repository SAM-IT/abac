<?php
declare(strict_types=1);

namespace test\resolvers;

use PHPUnit\Framework\TestCase;
use SamIT\abac\interfaces\Resolver;
use SamIT\abac\resolvers\ChainedResolver;
use SamIT\abac\values\Authorizable;

/**
 * Class ChainedResolverTest
 * @package test\resolvers
 * @covers \SamIT\abac\resolvers\ChainedResolver
 */
class ChainedResolverTest extends TestCase
{
    public function testFromSubjectUsesAll()
    {
        $resolvers = [];
        for ($i = mt_rand(1, 50); $i >0; $i--) {
            $resolvers[] = $resolver = $this->getMockBuilder(Resolver::class)->getMock();
            $resolver
                ->expects($this->once())->method('fromSubject')
                ->willReturn(null);
        }

        $chain = new ChainedResolver(...$resolvers);
        $chain->fromSubject(new \stdClass());
    }

    public function testToSubjectUsesAll()
    {
        $resolvers = [];
        for ($i = mt_rand(1, 50); $i >0; $i--) {
            $resolvers[] = $resolver = $this->getMockBuilder(Resolver::class)->getMock();
            $resolver
                ->expects($this->once())->method('toSubject')
                ->willReturn(null);
        }

        $chain = new ChainedResolver(...$resolvers);
        $chain->toSubject(new Authorizable('test', 'test'));
    }

    public function testFromSubjectStopsEarly()
    {
        $resolvers = [];
        for ($i = mt_rand(1, 50); $i >0; $i--) {
            $resolvers[] = $resolver = $this->getMockBuilder(Resolver::class)->getMock();
            $resolver
                ->expects($this->never())->method('fromSubject');
        }

        $resolver = $this->getMockBuilder(Resolver::class)->getMock();
        $resolver
            ->expects($this->once())->method('fromSubject')
            ->willReturn(new Authorizable('test', 'test'));
        $chain = new ChainedResolver($resolver, ...$resolvers);
        $chain->fromSubject(new \stdClass());
    }

    public function testToSubjectStopsEarly()
    {
        $resolvers = [];
        for ($i = mt_rand(1, 50); $i > 0; $i--) {
            $resolvers[] = $resolver = $this->getMockBuilder(Resolver::class)->getMock();
            $resolver
                ->expects($this->once())->method('toSubject')
                ->willReturn(null);
        }

        $expected = new \stdClass();
        $resolvers[] = $resolver = $this->getMockBuilder(Resolver::class)->getMock();
        $resolver
            ->expects($this->once())->method('toSubject')
            ->willReturn($expected);
        for ($i = mt_rand(1, 50); $i > 0; $i--) {
            $resolvers[] = $resolver = $this->getMockBuilder(Resolver::class)->getMock();
            $resolver
                ->expects($this->never())->method('toSubject');
        }


        $this->assertSame($expected, (new ChainedResolver(...$resolvers))->toSubject(new Authorizable('test', 'test')));
    }
}
