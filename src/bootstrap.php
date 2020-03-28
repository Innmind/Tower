<?php
declare(strict_types = 1);

namespace Innmind\Tower;

use Innmind\Server\Control\Server;
use Innmind\Url\Path;
use Innmind\Socket\{
    Serve,
    Event\ConnectionReady,
};
use Innmind\CLI\Commands;
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

function bootstrap(
    Server $server,
    Remote $remote,
    Ports $ports,
    Sockets $sockets,
    Path $config
): Commands {
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

    return new Commands(
        new Command\Trigger($run),
        new Command\Ping($configuration, $ping),
        new Command\Listen($ports, $server, $loop),
    );
}
