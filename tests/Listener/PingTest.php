<?php
declare(strict_types = 1);

namespace Tests\Innmind\Tower\Listener;

use Innmind\Tower\{
    Listener\Ping,
    Ping as NeighbourPing,
    Neighbour,
    Neighbour\Name,
    Configuration,
    Run,
};
use Innmind\Server\Control\{
    Server,
    Server\Processes,
};
use Innmind\Socket\{
    Event\DataReceived,
    Server\Connection,
};
use Innmind\Url\Url;
use Innmind\Immutable\{
    Set,
    Str,
};
use PHPUnit\Framework\TestCase;

class PingTest extends TestCase
{
    /**
     * @dataProvider invalidPayloads
     */
    public function testDoesNothingWhenInvalidPayload($payload)
    {
        $event = new DataReceived(
            $this->createMock(Connection::class),
            Str::of(json_encode($payload))
        );
        $server = $this->createMock(Server::class);
        $server
            ->expects($this->once())
            ->method('processes')
            ->willReturn($processes = $this->createMock(Processes::class));
        $processes
            ->expects($this->never())
            ->method('execute');
        $ping = new Ping(
            new Run(
                $server,
                new Configuration(
                    Set::of(Neighbour::class),
                    Set::of('string'),
                    Set::of('string', 'foo')
                ),
                $this->createMock(NeighbourPing::class)
            )
        );

        $this->assertNull($ping($event));
    }

    public function testRun()
    {
        $event = new DataReceived(
            $this->createMock(Connection::class),
            Str::of(json_encode(['tags' => ['foo', 'bar']]))
        );
        $ping = new Ping(
            new Run(
                $this->createMock(Server::class),
                new Configuration(
                    Set::of(
                        Neighbour::class,
                        $expected = new Neighbour(
                            new Name('watev'),
                            Url::fromString('example.com'),
                            'foo'
                        )
                    ),
                    Set::of('string'),
                    Set::of('string')
                ),
                $neighbourPing = $this->createMock(NeighbourPing::class)
            )
        );
        $neighbourPing
            ->expects($this->once())
            ->method('__invoke')
            ->with($expected, 'foo', 'bar');

        $this->assertNull($ping($event));
    }

    public function invalidPayloads(): array
    {
        return [
            ['foo'],
            [[]],
            [['tags' => '']],
        ];
    }
}
