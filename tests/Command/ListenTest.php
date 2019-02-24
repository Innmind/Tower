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
    Loop,
    Client\Internet,
    Internet\Transport,
};
use Innmind\EventBus\EventBus;
use Innmind\TimeContinuum\ElapsedPeriod;
use Innmind\Url\{
    Path,
    Url,
};
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
                $this->createMock(Server::class),
                new Loop(
                    $this->createMock(EventBus::class),
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
                $this->createMock(EventBus::class),
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
                    ->put('daemon', true)
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
                    ->withWorkingDirectory(getcwd())
                    ->withEnvironment('TOWER_CONFIG', 'config/config.yml.dist')
            );

        //wait for the process to open the connection
        sleep(1);

        $client = new Internet(
            Transport::tcp(),
            Url::fromString('//127.0.0.1:1337')->authority()
        );
        $client
            ->write(Str::of('{"tags":[]}'))
            ->close();

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
            (string) new Listen(
                $this->createMock(Server::class),
                new Loop(
                    $this->createMock(EventBus::class),
                    new ElapsedPeriod(42)
                )
            )
        );
    }
}
