<?php
declare(strict_types = 1);

namespace Tests\Innmind\Tower;

use Innmind\Tower\{
    Neighbour,
    Neighbour\Name,
};
use Innmind\Url\UrlInterface;
use Innmind\Immutable\SetInterface;
use PHPUnit\Framework\TestCase;

class NeighbourTest extends TestCase
{
    public function testInterface()
    {
        $neighbour = new Neighbour(
            $name = new Name('foo'),
            $url = $this->createMock(UrlInterface::class),
            'bar',
            'baz'
        );

        $this->assertSame($name, $neighbour->name());
        $this->assertSame($url, $neighbour->url());
        $this->assertInstanceOf(SetInterface::class, $neighbour->tags());
        $this->assertSame('string', (string) $neighbour->tags()->type());
        $this->assertSame(['bar', 'baz'], $neighbour->tags()->toPrimitive());
        $this->assertTrue($neighbour->matches('bar'));
        $this->assertTrue($neighbour->matches('baz'));
        $this->assertTrue($neighbour->matches('bar', 'baz'));
        $this->assertTrue($neighbour->matches('foo', 'baz'));
        $this->assertFalse($neighbour->matches('foo', 'watev'));
    }
}
