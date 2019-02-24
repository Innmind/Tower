<?php
declare(strict_types = 1);

namespace Tests\Innmind\Tower;

use function Innmind\Tower\bootstrap;
use Innmind\Server\Control\Server;
use Innmind\CLI\Commands;
use Innmind\Url\Path;
use PHPUnit\Framework\TestCase;

class BootstrapTest extends TestCase
{
    public function testInvokation()
    {
        $commands = bootstrap(
            $this->createMock(Server::class),
            new Path('config/config.yml.dist')
        );

        $this->assertInstanceOf(Commands::class, $commands);
    }
}
