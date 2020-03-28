<?php
declare(strict_types = 1);

namespace Tests\Innmind\Tower;

use Innmind\Tower\{
    Run,
    Configuration,
    Neighbour,
    Neighbour\Name,
    Exception\ActionFailed,
    Ping,
};
use Innmind\Url\Url;
use Innmind\Server\Control\{
    Server,
    Server\Processes,
    Server\Process,
    Server\Process\Output,
    Server\Process\ExitCode,
};
use Innmind\Immutable\Set;
use PHPUnit\Framework\TestCase;

class RunTest extends TestCase
{
    public function testInvoke()
    {
        $server = $this->createMock(Server::class);
        $server
            ->expects($this->once())
            ->method('processes')
            ->willReturn($processes = $this->createMock(Processes::class));
        $run = new Run(
            $server,
            new Configuration(
                Set::of(Neighbour::class),
                Set::of('string', 'env1', 'env2'),
                Set::of('string', 'action1', 'action2')
            ),
            $this->createMock(Ping::class)
        );
        $processes
            ->expects($this->at(0))
            ->method('execute')
            ->with($this->callback(static function($command): bool {
                return $command->toString() === 'env1';
            }))
            ->willReturn($process = $this->createMock(Process::class));
        $process
            ->expects($this->once())
            ->method('wait');
        $process
            ->expects($this->once())
            ->method('output')
            ->willReturn($output = $this->createMock(Output::class));
        $output
            ->expects($this->once())
            ->method('toString')
            ->willReturn("ENV1=foo\n");
        $processes
            ->expects($this->at(1))
            ->method('execute')
            ->with($this->callback(static function($command): bool {
                return $command->toString() === 'env2' &&
                    $command->environment()->get('ENV1') === 'foo';
            }))
            ->willReturn($process = $this->createMock(Process::class));
        $process
            ->expects($this->once())
            ->method('wait');
        $process
            ->expects($this->once())
            ->method('output')
            ->willReturn($output = $this->createMock(Output::class));
        $output
            ->expects($this->once())
            ->method('toString')
            ->willReturn("ENV2=bar\n");
        $processes
            ->expects($this->at(2))
            ->method('execute')
            ->with($this->callback(static function($command): bool {
                return $command->toString() === 'action1' &&
                    $command->environment()->get('ENV1') === 'foo' &&
                    $command->environment()->get('ENV2') === 'bar';
            }))
            ->willReturn($process = $this->createMock(Process::class));
        $process
            ->expects($this->once())
            ->method('wait');
        $process
            ->expects($this->once())
            ->method('exitCode')
            ->willReturn(new ExitCode(0));
        $processes
            ->expects($this->at(3))
            ->method('execute')
            ->with($this->callback(static function($command): bool {
                return $command->toString() === 'action2' &&
                    $command->environment()->get('ENV1') === 'foo' &&
                    $command->environment()->get('ENV2') === 'bar';
            }))
            ->willReturn($process = $this->createMock(Process::class));
        $process
            ->expects($this->once())
            ->method('wait');
        $process
            ->expects($this->once())
            ->method('exitCode')
            ->willReturn(new ExitCode(0));

        $this->assertNull($run());
    }

    public function testThrowWhenActionFailed()
    {
        $this->expectException(ActionFailed::class);
        $this->expectExceptionMessage('action');

        $server = $this->createMock(Server::class);
        $server
            ->expects($this->once())
            ->method('processes')
            ->willReturn($processes = $this->createMock(Processes::class));
        $run = new Run(
            $server,
            new Configuration(
                Set::of(Neighbour::class),
                Set::of('string'),
                Set::of('string', 'action')
            ),
            $this->createMock(Ping::class)
        );

        $processes
            ->expects($this->once())
            ->method('execute')
            ->with($this->callback(static function($command): bool {
                return $command->toString() === 'action';
            }))
            ->willReturn($process = $this->createMock(Process::class));
        $process
            ->expects($this->once())
            ->method('wait');
        $process
            ->expects($this->once())
            ->method('exitCode')
            ->willReturn(new ExitCode(1));

        $run();
    }

    public function testPingNeighbours()
    {
        $server = $this->createMock(Server::class);
        $server
            ->expects($this->once())
            ->method('processes')
            ->willReturn($processes = $this->createMock(Processes::class));
        $neighbour1 = new Neighbour(
            new Name('1'),
            Url::of('example.com'),
            'foo'
        );
        $neighbour2 = new Neighbour(
            new Name('2'),
            Url::of('example.com'),
            'bar'
        );
        $neighbour3 = new Neighbour(
            new Name('3'),
            Url::of('example.com'),
            'baz'
        );
        $run = new Run(
            $server,
            new Configuration(
                Set::of(Neighbour::class, $neighbour1, $neighbour2, $neighbour3),
                Set::of('string'),
                Set::of('string')
            ),
            $ping = $this->createMock(Ping::class)
        );
        $ping
            ->expects($this->at(0))
            ->method('__invoke')
            ->with($neighbour1, 'foo', 'baz');
        $ping
            ->expects($this->at(1))
            ->method('__invoke')
            ->with($neighbour3, 'foo', 'baz');
        $ping
            ->expects($this->exactly(2))
            ->method('__invoke');

        $this->assertNull($run('foo', 'baz'));
    }
}
