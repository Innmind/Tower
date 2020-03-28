<?php
declare(strict_types = 1);

namespace Innmind\Tower\Command;

use Innmind\CLI\{
    Command,
    Command\Arguments,
    Command\Options,
    Environment,
};
use Innmind\Server\Control\{
    Server,
    Server\Command as ServerCommand,
};
use Innmind\Socket\{
    Internet\Transport,
    Serve,
};
use Innmind\OperatingSystem\Ports;
use Innmind\IP\IPv4;
use Innmind\Url\Authority\Port;

final class Listen implements Command
{
    private Ports $ports;
    private Server $server;
    private Serve $serve;

    public function __construct(Ports $ports, Server $server, Serve $serve)
    {
        $this->ports = $ports;
        $this->server = $server;
        $this->serve = $serve;
    }

    public function __invoke(Environment $env, Arguments $arguments, Options $options): void
    {
        if ($options->contains('daemon')) {
            $this
                ->server
                ->processes()
                ->execute(
                    ServerCommand::background('tower')
                        ->withArgument('listen')
                        ->withArgument($arguments->get('port'))
                        ->withWorkingDirectory($env->workingDirectory())
                );

            return;
        }

        do {
            try {
                $socket = $this->ports->open(
                    Transport::tcp(),
                    IPv4::of('127.0.0.1'),
                    Port::of((int) $arguments->get('port'))
                );

                ($this->serve)($socket);
            } catch (\Throwable $e) {
                //pass
            }
            //the while is to make sure we never exit
        } while (true);
    }

    public function toString(): string
    {
        return <<<USAGE
listen port -d|--daemon

Will open a tcp socket on given port waiting for incoming ping

The "d" option will run this command in the background
USAGE;
    }
}
