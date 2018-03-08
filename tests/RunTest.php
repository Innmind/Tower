<?php
declare(strict_types = 1);

namespace Tests\Innmind\Tower;

use Innmind\Tower\{
    Run,
    Configuration,
    Neighbour,
    Exception\ActionFailed,
};
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
            )
        );
        $processes
            ->expects($this->at(0))
            ->method('execute')
            ->with($this->callback(static function($command): bool {
                return (string) $command === 'env1';
            }))
            ->willReturn($process = $this->createMock(Process::class));
        $process
            ->expects($this->once())
            ->method('wait')
            ->will($this->returnSelf());
        $process
            ->expects($this->once())
            ->method('output')
            ->willReturn($output = $this->createMock(Output::class));
        $output
            ->expects($this->once())
            ->method('__toString')
            ->willReturn("ENV1=foo\n");
        $processes
            ->expects($this->at(1))
            ->method('execute')
            ->with($this->callback(static function($command): bool {
                return (string) $command === 'env2' &&
                    $command->environment()->get('ENV1') === 'foo';
            }))
            ->willReturn($process = $this->createMock(Process::class));
        $process
            ->expects($this->once())
            ->method('wait')
            ->will($this->returnSelf());
        $process
            ->expects($this->once())
            ->method('output')
            ->willReturn($output = $this->createMock(Output::class));
        $output
            ->expects($this->once())
            ->method('__toString')
            ->willReturn("ENV2=bar\n");
        $processes
            ->expects($this->at(2))
            ->method('execute')
            ->with($this->callback(static function($command): bool {
                return (string) $command === 'action1' &&
                    $command->environment()->get('ENV1') === 'foo' &&
                    $command->environment()->get('ENV2') === 'bar';
            }))
            ->willReturn($process = $this->createMock(Process::class));
        $process
            ->expects($this->once())
            ->method('wait')
            ->will($this->returnSelf());
        $process
            ->expects($this->once())
            ->method('exitCode')
            ->willReturn(new ExitCode(0));
        $processes
            ->expects($this->at(3))
            ->method('execute')
            ->with($this->callback(static function($command): bool {
                return (string) $command === 'action2' &&
                    $command->environment()->get('ENV1') === 'foo' &&
                    $command->environment()->get('ENV2') === 'bar';
            }))
            ->willReturn($process = $this->createMock(Process::class));
        $process
            ->expects($this->once())
            ->method('wait')
            ->will($this->returnSelf());
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
            )
        );

        $processes
            ->expects($this->once())
            ->method('execute')
            ->with($this->callback(static function($command): bool {
                return (string) $command === 'action';
            }))
            ->willReturn($process = $this->createMock(Process::class));
        $process
            ->expects($this->once())
            ->method('wait')
            ->will($this->returnSelf());
        $process
            ->expects($this->once())
            ->method('exitCode')
            ->willReturn(new ExitCode(1));

        $run();
    }
}
