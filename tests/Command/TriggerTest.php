<?php
declare(strict_types = 1);

namespace Tests\Innmind\Tower\Command;

use Innmind\Tower\{
    Command\Trigger,
    Run,
    Neighbour,
    Neighbour\Name,
    Configuration,
    Ping,
};
use Innmind\CLI\{
    Command,
    Command\Arguments,
    Command\Options,
    Environment,
};
use Innmind\Url\Url;
use Innmind\Server\Control\Server;
use Innmind\Immutable\{
    Set,
    Map,
};
use PHPUnit\Framework\TestCase;

class TriggerTest extends TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(
            Command::class,
            new Trigger(
                new Run(
                    $this->createMock(Server::class),
                    new Configuration(
                        Set::of(Neighbour::class),
                        Set::of('string'),
                        Set::of('string')
                    ),
                    $this->createMock(Ping::class)
                )
            )
        );
    }

    public function testInvoke()
    {
        $trigger = new Trigger(
            new Run(
                $this->createMock(Server::class),
                new Configuration(
                    Set::of(
                        Neighbour::class,
                        $neighbour = new Neighbour(
                            new Name('foo'),
                            Url::fromString('example.com'),
                            'foo'
                        )
                    ),
                    Set::of('string'),
                    Set::of('string')
                ),
                $ping = $this->createMock(Ping::class)
            )
        );
        $ping
            ->expects($this->once())
            ->method('__invoke')
            ->with($neighbour, 'foo', 'bar');

        $this->assertNull($trigger(
            $this->createMock(Environment::class),
            new Arguments,
            new Options(
                (new Map('string', 'mixed'))
                    ->put('tags', 'foo , bar')
            )
        ));
    }

    public function testUsage()
    {
        $expected = <<<USAGE
trigger --tags=

Will call the actions configured to happen when this server is pinged

The "tags" controls the neighbours (having one of the tags) to be pinged.
Specified tags are forwarded to each neighbours when pinged.
USAGE;

        $this->assertSame(
            $expected,
            (string) new Trigger(
                new Run(
                    $this->createMock(Server::class),
                    new Configuration(
                        Set::of(Neighbour::class),
                        Set::of('string'),
                        Set::of('string')
                    ),
                    $this->createMock(Ping::class)
                )
            )
        );
    }
}