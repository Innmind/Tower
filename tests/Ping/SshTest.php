<?php
declare(strict_types = 1);

namespace Tests\Innmind\Tower\Ping;

use Innmind\Tower\{
    Ping\Ssh,
    Ping,
    Neighbour,
    Neighbour\Name,
};
use Innmind\Url\Url;
use Innmind\Server\Control\{
    Server,
    Server\Processes,
};
use PHPUnit\Framework\TestCase;

class SshTest extends TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(
            Ping::class,
            new Ssh($this->createMock(Server::class))
        );
    }

    public function testInvoke()
    {
        $ping = new Ssh(
            $server = $this->createMock(Server::class)
        );
        $server
            ->expects($this->once())
            ->method('processes')
            ->willReturn($processes = $this->createMock(Processes::class));
        $processes
            ->expects($this->once())
            ->method('execute')
            ->with($this->callback(static function($command): bool {
                return (string) $command === "ssh '-p' '1337' 'baptouuuu@example.com' 'cd /path/to/config && tower '\''trigger'\'' '\''--tags=foo,bar'\'''";
            }));

        $this->assertNull($ping(new Neighbour(
            new Name('foo'),
            Url::fromString('ssh://baptouuuu@example.com:1337/path/to/config')
        ), 'foo', 'bar'));
    }
}
