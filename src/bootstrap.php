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
use Innmind\OperatingSystem\Remote;
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
    PathInterface $config
): Commands {
    $configuration = Configuration::load(
        $config,
        new Configuration\Yaml
    );
    $ping = new Ping\Delegate(
        Map::of('string', Ping::class)
            ('tcp', new Ping\Tcp($remote))
            ('ssh', new Ping\Ssh($remote))
    );

    $run = new Run($server, $configuration, $ping);
    $loop = new Loop(
        new EventBus(
            Map::of('string', SetInterface::class)
                (DataReceived::class, Set::of('callable', new Listener\Ping($run)))
        ),
        new ElapsedPeriod(3600000) // 1 hour
    );

    return new Commands(
        new Command\Trigger($run),
        new Command\Ping($configuration, $ping),
        new Command\Listen($server, $loop)
    );
}
