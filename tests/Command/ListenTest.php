<?php
declare(strict_types = 1);

namespace Tests\Innmind\Tower\Command;

use Innmind\Tower\{
    Command\Listen,
};
use Innmind\CLI\{
    Command,
    Command\Arguments,
    Command\Options,
    Environment,
};
use Innmind\Server\Control\{
    Server,
    Server\Processes,
};
use Innmind\Socket\Loop;
use Innmind\EventBus\EventBusInterface;
use Innmind\TimeContinuum\ElapsedPeriod;
use Innmind\Url\Path;
use Innmind\Immutable\Map;
use PHPUnit\Framework\TestCase;

class ListenTest extends TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(
            Command::class,
            new Listen(
                $this->createMock(Server::class),
                new Loop(
                    $this->createMock(EventBusInterface::class),
                    new ElapsedPeriod(42)
                )
            )
        );
    }

    public function testDaemonize()
    {
        $listen = new Listen(
            $server = $this->createMock(Server::class),
            new Loop(
                $this->createMock(EventBusInterface::class),
                new ElapsedPeriod(42)
            )
        );
        $server
            ->expects($this->once())
            ->method('processes')
            ->willReturn($processes = $this->createMock(Processes::class));
        $processes
            ->expects($this->once())
            ->method('execute')
            ->with($this->callback(static function($command): bool {
                return (string) $command === "tower 'listen' '1337'" &&
                    $command->toBeRunInBackground() &&
                    $command->workingDirectory() === '/working/directory';
            }));

        $env = $this->createMock(Environment::class);
        $env
            ->expects($this->once())
            ->method('workingDirectory')
            ->willReturn(new Path('/working/directory'));

        $this->assertNull($listen(
            $env,
            new Arguments(
                (new Map('string', 'mixed'))
                    ->put('port', '1337')
            ),
            new Options(
                (new Map('string', 'mixed'))
                    ->put('d', true)
            )
        ));
    }

    public function testUsage()
    {
        $expected = <<<USAGE
listen port -d

Will open a tcp socket on given port waiting for incoming ping

The "d" option will run this command in the background
USAGE;

        $this->assertSame(
            $expected,
            (string) new Listen(
                $this->createMock(Server::class),
                new Loop(
                    $this->createMock(EventBusInterface::class),
                    new ElapsedPeriod(42)
                )
            )
        );
    }
}
