<?php
declare(strict_types = 1);

namespace Innmind\Tower;

use Innmind\Tower\Command\{
    Trigger,
    Ping as PingCommand,
    Listen,
};
use Innmind\Server\Control\Server;
use Innmind\Url\Path;
use Innmind\Socket\{
    Serve,
    Event\ConnectionReady,
};
use Innmind\CLI\Command;
use Innmind\OperatingSystem\{
    Remote,
    Ports,
    Sockets,
};
use Innmind\EventBus\EventBus;
use Innmind\TimeContinuum\Earth\ElapsedPeriod;
use Innmind\Immutable\{
    Map,
    Set,
};

/**
 * @return list<Command>
 */
function bootstrap(
    Server $server,
    Remote $remote,
    Ports $ports,
    Sockets $sockets,
    Path $config
): array {
    $configuration = (new Configuration\Yaml)($config);
    /**
     * @psalm-suppress InvalidScalarArgument
     * @psalm-suppress InvalidArgument
     */
    $ping = new Ping\Delegate(
        Map::of('string', Ping::class)
            ('tcp', new Ping\Tcp($remote))
            ('ssh', new Ping\Ssh($remote)),
    );

    $run = new Run($server, $configuration, $ping);
    /**
     * @psalm-suppress InvalidScalarArgument
     * @psalm-suppress InvalidArgument
     */
    $loop = new Serve(
        new EventBus\Map(
            Map::of('string', 'callable')
                (ConnectionReady::class, new Listener\Ping($run)),
        ),
        $sockets->watch(new ElapsedPeriod(3600000)), // 1 hour
    );

    return [
        new Trigger($run),
        new PingCommand($configuration, $ping),
        new Listen($ports, $server, $loop),
    ];
}
