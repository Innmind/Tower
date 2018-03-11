<?php
declare(strict_types = 1);

namespace Tests\Innmind\Tower\Command;

use Innmind\Tower\{
    Command\Ping,
    Configuration,
    Neighbour,
    Neighbour\Name,
    Ping as ServerPing,
};
use Innmind\CLI\{
    Command,
    Command\Arguments,
    Command\Options,
    Environment,
};
use Innmind\Url\Url;
use Innmind\Immutable\{
    Set,
    Map,
};
use PHPUnit\Framework\TestCase;

class PingTest extends TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(
            Command::class,
            new Ping(
                new Configuration(
                    Set::of(Neighbour::class),
                    Set::of('string'),
                    Set::of('string')
                ),
                $this->createMock(ServerPing::class)
            )
        );
    }

    public function testInvoke()
    {
        $ping = new Ping(
            new Configuration(
                Set::of(
                    Neighbour::class,
                    new Neighbour(
                        new Name('foo'),
                        Url::fromString('example.com')
                    ),
                    $expected = new Neighbour(
                        new Name('bar'),
                        Url::fromString('example2.com')
                    )
                ),
                Set::of('string'),
                Set::of('string')
            ),
            $serverPing = $this->createMock(ServerPing::class)
        );
        $serverPing
            ->expects($this->once())
            ->method('__invoke')
            ->with($expected, 'foobar', 'baz');

        $this->assertNull($ping(
            $this->createMock(Environment::class),
            new Arguments(
                (new Map('string', 'mixed'))
                    ->put('server', 'bar')
            ),
            new Options(
                (new Map('string', 'mixed'))
                    ->put('tags', 'foobar , baz')
            )
        ));
    }

    public function testUsage()
    {
        $expected = <<<USAGE
ping server --tags=

Send a ping to a configured server in order to trigger its behaviour

The ping will propagate to all the neighbours of the server. In case you
provide the "tags" option (comma separated list) only the neighbours flagged
with one the tgas will be pinged.
USAGE;

        $this->assertSame(
            $expected,
            (string) new Ping(
                new Configuration(
                    Set::of(Neighbour::class),
                    Set::of('string'),
                    Set::of('string')
                ),
                $this->createMock(ServerPing::class)
            )
        );
    }
}
