<?php
declare(strict_types = 1);

namespace Tests\Innmind\Tower\Ping;

use Innmind\Tower\{
    Ping\Tcp,
    Ping,
    Neighbour,
    Neighbour\Name,
};
use Innmind\Url\{
    Url,
    Authority\Port,
};
use Innmind\Socket\{
    Server\Internet,
    Internet\Transport,
};
use Innmind\IP\IPv4;
use PHPUnit\Framework\TestCase;

class TcpTest extends TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(Ping::class, new Tcp);
    }

    public function testInvoke()
    {
        $neighbour = new Neighbour(
            new Name('foo'),
            Url::fromString('tcp://127.0.0.1:1338')
        );
        $ping = new Tcp;
        $socket = new Internet(
            Transport::tcp(),
            new IPv4('127.0.0.1'),
            new Port(1338)
        );

        $this->assertNull($ping($neighbour, 'foo', 'bar'));
        $connection = $socket->accept();
        $data = $connection->read();
        $connection->close();
        $this->assertSame(
            ['tags' => ['foo', 'bar']],
            json_decode((string) $data, true)
        );
        $socket->close();
    }
}
