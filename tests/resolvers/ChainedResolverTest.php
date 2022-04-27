<?php

declare(strict_types=1);

namespace test\resolvers;

use PHPUnit\Framework\TestCase;
use SamIT\abac\exceptions\UnresolvableException;
use SamIT\abac\interfaces\Resolver;
use SamIT\abac\resolvers\ChainedResolver;
use SamIT\abac\values\Authorizable;

/**
 * @covers \SamIT\abac\resolvers\ChainedResolver
 */
final class ChainedResolverTest extends TestCase
{
    public function testFromSubjectUsesAll(): void
    {
        $resolvers = [];
        for ($i = mt_rand(1, 50); $i > 0; $i--) {
            $resolvers[] = $resolver = $this->getMockBuilder(Resolver::class)->getMock();
            $resolver
                ->expects(self::once())->method('fromSubject')
                ->willThrowException(new UnresolvableException());
        }

        $chain = new ChainedResolver(...$resolvers);
        $this->expectException(UnresolvableException::class);
        $chain->fromSubject(new \stdClass());
    }

    public function testToSubjectUsesAll(): void
    {
        $resolvers = [];
        for ($i = mt_rand(1, 50); $i > 0; $i--) {
            $resolvers[] = $resolver = $this->getMockBuilder(Resolver::class)->getMock();
            $resolver
                ->expects(self::once())->method('toSubject')
                ->willThrowException(new UnresolvableException());
        }

        $chain = new ChainedResolver(...$resolvers);
        $this->expectException(UnresolvableException::class);
        $chain->toSubject(new Authorizable('test', 'test'));
    }

    public function testFromSubjectStopsEarly(): void
    {
        $resolvers = [];
        for ($i = mt_rand(1, 50); $i > 0; $i--) {
            $resolvers[] = $resolver = $this->getMockBuilder(Resolver::class)->getMock();
            $resolver
                ->expects(self::never())->method('fromSubject');
        }

        $resolver = $this->getMockBuilder(Resolver::class)->getMock();
        $resolver
            ->expects(self::once())->method('fromSubject')
            ->willReturn(new Authorizable('test', 'test'));
        $chain = new ChainedResolver($resolver, ...$resolvers);
        $chain->fromSubject(new \stdClass());
    }

    public function testToSubjectStopsEarly(): void
    {
        $resolvers = [];
        for ($i = mt_rand(1, 50); $i > 0; $i--) {
            $resolvers[] = $resolver = $this->getMockBuilder(Resolver::class)->getMock();
            $resolver
                ->expects(self::once())->method('toSubject')
                ->willThrowException(new UnresolvableException());
        }

        $expected = new \stdClass();
        $resolvers[] = $resolver = $this->getMockBuilder(Resolver::class)->getMock();
        $resolver
            ->expects(self::once())->method('toSubject')
            ->willReturn($expected);
        for ($i = mt_rand(1, 50); $i > 0; $i--) {
            $resolvers[] = $resolver = $this->getMockBuilder(Resolver::class)->getMock();
            $resolver
                ->expects(self::never())->method('toSubject');
        }


        self::assertSame($expected, (new ChainedResolver(...$resolvers))->toSubject(new Authorizable('test', 'test')));
    }
}
