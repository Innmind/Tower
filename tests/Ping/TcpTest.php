<?php
declare(strict_types = 1);

namespace Tests\Innmind\Tower\Ping;

use Innmind\Tower\{
    Ping\Tcp,
    Ping,
    Neighbour,
    Neighbour\Name,
};
use Innmind\Url\Url;
use Innmind\Socket\{
    Client,
    Internet\Transport,
};
use Innmind\OperatingSystem\Remote;
use Innmind\Immutable\Str;
use PHPUnit\Framework\TestCase;

class TcpTest extends TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(Ping::class, new Tcp($this->createMock(Remote::class)));
    }

    public function testInvoke()
    {
        $neighbour = new Neighbour(
            new Name('foo'),
            Url::of('tcp://127.0.0.1:1338')
        );
        $ping = new Tcp(
            $remote = $this->createMock(Remote::class)
        );
        $remote
            ->expects($this->once())
            ->method('socket')
            ->with(
                Transport::tcp(),
                $neighbour->url()->authority()
            )
            ->willReturn($client = $this->createMock(Client::class));
        $client
            ->expects($this->at(0))
            ->method('write')
            ->with(Str::of('{"tags":["foo","bar"]}'))
            ->will($this->returnSelf());
        $client
            ->expects($this->at(1))
            ->method('close');

        $this->assertNull($ping($neighbour, 'foo', 'bar'));
    }
}
