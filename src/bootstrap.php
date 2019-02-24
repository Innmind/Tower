<?php
declare(strict_types = 1);

namespace Innmind\Tower;

use Innmind\Server\Control\Server;
use Innmind\Url\PathInterface;
use Innmind\Socket\{
    Loop,
    Event\DataReceived,
};
use Innmind\CLI\Commands;
use Innmind\OperatingSystem\{
    Remote,
    Ports,
};
use Innmind\EventBus\EventBus;
use Innmind\TimeContinuum\ElapsedPeriod;
use Innmind\Immutable\{
    Map,
    SetInterface,
    Set,
};

function bootstrap(
    Server $server,
    Remote $remote,
    Ports $ports,
    PathInterface $config
): Commands {
    $configuration = (new Configuration\Yaml)($config);
    $ping = new Ping\Delegate(
        Map::of('string', Ping::class)
            ('tcp', new Ping\Tcp($remote))
            ('ssh', new Ping\Ssh($remote))
    );

    $run = new Run($server, $configuration, $ping);
    $loop = new Loop(
        new EventBus\Map(
            Map::of('string', 'callable')
                (DataReceived::class, new Listener\Ping($run))
        ),
        new ElapsedPeriod(3600000) // 1 hour
    );

    return new Commands(
        new Command\Trigger($run),
        new Command\Ping($configuration, $ping),
        new Command\Listen($ports, $server, $loop)
    );
}
