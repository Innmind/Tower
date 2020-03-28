<?php
declare(strict_types = 1);

namespace Tests\Innmind\Tower\Ping;

use Innmind\Tower\{
    Ping\Delegate,
    Ping,
    Neighbour,
    Neighbour\Name,
    Exception\SchemeNotSupported,
};
use Innmind\Url\Url;
use Innmind\Immutable\Map;
use PHPUnit\Framework\TestCase;

class DelegateTest extends TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(
            Ping::class,
            new Delegate(
                Map::of('string', Ping::class)
            )
        );
    }

    public function testThrowWhenInvalidPingKey()
    {
        $this->expectException(\TypeError::class);
        $this->expectExceptionMessage('Argument 1 must be of type Map<string, Innmind\Tower\Ping>');

        new Delegate(Map::of('int', Ping::class));
    }

    public function testThrowWhenInvalidPingValue()
    {
        $this->expectException(\TypeError::class);
        $this->expectExceptionMessage('Argument 1 must be of type Map<string, Innmind\Tower\Ping>');

        new Delegate(Map::of('string', 'callable'));
    }

    public function testInvoke()
    {
        $neighbour = new Neighbour(
            new Name('foo'),
            Url::of('tcp://host.com')
        );
        $tags = ['foo', 'bar'];
        $ping = new Delegate(
            (Map::of('string', Ping::class))
                ->put('ssh', $mock1 = $this->createMock(Ping::class))
                ->put('tcp', $mock2 = $this->createMock(Ping::class))
        );
        $mock1
            ->expects($this->never())
            ->method('__invoke');
        $mock2
            ->expects($this->once())
            ->method('__invoke')
            ->with($neighbour, ...$tags);

        $this->assertNull($ping($neighbour, ...$tags));
    }

    public function testThrowWhenSchemeNotSupported()
    {
        $this->expectException(SchemeNotSupported::class);
        $this->expectExceptionMessage('tcp');

        $ping = new Delegate(Map::of('string', Ping::class));

        $ping(new Neighbour(
            new Name('foo'),
            Url::of('tcp://example.com')
        ));
    }
}
