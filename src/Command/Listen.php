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
    Server\Internet,
    Internet\Transport,
    Loop,
};
use Innmind\IP\IPv4;
use Innmind\Url\Authority\Port;

final class Listen implements Command
{
    private $server;
    private $loop;

    public function __construct(Server $server, Loop $loop)
    {
        $this->server = $server;
        $this->loop = $loop;
    }

    public function __invoke(Environment $env, Arguments $arguments, Options $options): void
    {
        if ($options->contains('d')) {
            $this
                ->server
                ->processes()
                ->execute(
                    ServerCommand::background('tower')
                        ->withArgument('listen')
                        ->withArgument($arguments->get('port'))
                        ->withWorkingDirectory((string) $env->workingDirectory())
                );

            return;
        }

        do {
            try {
                $socket = new Internet(
                    Transport::tcp(),
                    new IPv4('127.0.0.1'),
                    new Port((int) $arguments->get('port'))
                );

                ($this->loop)($socket);
            } catch (\Throwable $e) {
                //pass
            }
            //the while is to make sure we never exit
        } while (true);
    }

    public function __toString(): string
    {
        return <<<USAGE
listen port -d

Will open a tcp socket on given port waiting for incoming ping

The "d" option will run this command in the background
USAGE;
    }
}
