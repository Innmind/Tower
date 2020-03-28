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
    ServerFactory,
    Server\Processes,
    Server\Command as ServerCommand,
};
use Innmind\Socket\{
    Serve,
    Client\Internet,
    Internet\Transport,
};
use Innmind\Stream\Watch;
use Innmind\EventBus\EventBus;
use Innmind\Url\{
    Path,
    Url,
};
use Innmind\OperatingSystem\Ports;
use Innmind\Immutable\{
    Map,
    Str,
};
use PHPUnit\Framework\TestCase;

class ListenTest extends TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(
            Command::class,
            new Listen(
                $this->createMock(Ports::class),
                $this->createMock(Server::class),
                new Serve(
                    $this->createMock(EventBus::class),
                    $this->createMock(Watch::class),
                )
            )
        );
    }

    public function testDaemonize()
    {
        $listen = new Listen(
            $this->createMock(Ports::class),
            $server = $this->createMock(Server::class),
            new Serve(
                $this->createMock(EventBus::class),
                $this->createMock(Watch::class),
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
                return $command->toString() === "tower 'listen' '1337'" &&
                    $command->toBeRunInBackground() &&
                    $command->workingDirectory()->toString() === '/working/directory';
            }));

        $env = $this->createMock(Environment::class);
        $env
            ->expects($this->once())
            ->method('workingDirectory')
            ->willReturn(Path::of('/working/directory'));

        $this->assertNull($listen(
            $env,
            new Arguments(
                Map::of('string', 'string')
                    ('port', '1337')
            ),
            new Options(
                Map::of('string', 'string')
                    ('daemon', '')
            )
        ));
    }

    public function testInvoke()
    {
        $server = ServerFactory::build();
        $process = $server
            ->processes()
            ->execute(
                ServerCommand::foreground('./tower')
                    ->withArgument('listen')
                    ->withArgument('1337')
                    ->withWorkingDirectory(Path::of(getcwd().'/'))
                    ->withEnvironment('TOWER_CONFIG', 'config/config.yml.dist')
            );

        //wait for the process to open the connection
        sleep(1);

        $client = new Internet(
            Transport::tcp(),
            Url::of('//127.0.0.1:1337')->authority()
        );
        $client->write(Str::of('{"tags":[]}'));
        $client->close();

        $this->assertTrue($process->isRunning());
        posix_kill($process->pid()->toInt(), SIGKILL);
    }

    public function testUsage()
    {
        $expected = <<<USAGE
listen port -d|--daemon

Will open a tcp socket on given port waiting for incoming ping

The "d" option will run this command in the background
USAGE;

        $this->assertSame(
            $expected,
            (new Listen(
                $this->createMock(Ports::class),
                $this->createMock(Server::class),
                new Serve(
                    $this->createMock(EventBus::class),
                    $this->createMock(Watch::class),
                )
            ))->toString()
        );
    }
}
