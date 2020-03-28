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
use Innmind\OperatingSystem\Remote;
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
            new Ssh($this->createMock(Remote::class))
        );
    }

    public function testInvoke()
    {
        $neighbour = new Neighbour(
            new Name('foo'),
            Url::of('ssh://baptouuuu@example.com:1337/path/to/config')
        );

        $ping = new Ssh(
            $remote = $this->createMock(Remote::class)
        );
        $remote
            ->expects($this->once())
            ->method('ssh')
            ->with($neighbour->url())
            ->willReturn($server = $this->createMock(Server::class));
        $server
            ->expects($this->once())
            ->method('processes')
            ->willReturn($processes = $this->createMock(Processes::class));
        $processes
            ->expects($this->once())
            ->method('execute')
            ->with($this->callback(static function($command): bool {
                return $command->toString() === "tower 'trigger' '--tags=foo,bar'"
                    && $command->workingDirectory()->toString() === '/path/to/config';
            }));

        $this->assertNull($ping($neighbour, 'foo', 'bar'));
    }
}
