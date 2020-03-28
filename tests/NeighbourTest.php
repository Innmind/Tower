<?php
declare(strict_types = 1);

namespace Tests\Innmind\Tower;

use Innmind\Tower\{
    Neighbour,
    Neighbour\Name,
};
use Innmind\Url\Url;
use Innmind\Immutable\Set;
use function Innmind\Immutable\unwrap;
use PHPUnit\Framework\TestCase;

class NeighbourTest extends TestCase
{
    public function testInterface()
    {
        $neighbour = new Neighbour(
            $name = new Name('foo'),
            $url = Url::of('example.com'),
            'bar',
            'baz'
        );

        $this->assertSame($name, $neighbour->name());
        $this->assertSame($url, $neighbour->url());
        $this->assertInstanceOf(Set::class, $neighbour->tags());
        $this->assertSame('string', (string) $neighbour->tags()->type());
        $this->assertSame(['bar', 'baz'], unwrap($neighbour->tags()));
        $this->assertTrue($neighbour->matches('bar'));
        $this->assertTrue($neighbour->matches('baz'));
        $this->assertTrue($neighbour->matches('bar', 'baz'));
        $this->assertTrue($neighbour->matches('foo', 'baz'));
        $this->assertTrue($neighbour->matches());
        $this->assertFalse($neighbour->matches('foo', 'watev'));
    }
}
