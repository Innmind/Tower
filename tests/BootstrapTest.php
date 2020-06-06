<?php
declare(strict_types = 1);

namespace Tests\Innmind\Tower;

use function Innmind\Tower\bootstrap;
use Innmind\Server\Control\Server;
use Innmind\CLI\Commands;
use Innmind\Url\Path;
use Innmind\OperatingSystem\{
    Remote,
    Ports,
    Sockets,
};
use PHPUnit\Framework\TestCase;

class BootstrapTest extends TestCase
{
    public function testInvokation()
    {
        $commands = bootstrap(
            $this->createMock(Server::class),
            $this->createMock(Remote::class),
            $this->createMock(Ports::class),
            $this->createMock(Sockets::class),
            Path::of('config/config.yml.dist')
        );

        $this->assertIsArray($commands);
        $this->assertCount(3, $commands);
    }
}
