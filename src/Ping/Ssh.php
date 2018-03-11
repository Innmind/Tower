<?php
declare(strict_types = 1);

namespace Innmind\Tower\Ping;

use Innmind\Tower\{
    Ping,
    Neighbour,
};
use Innmind\Server\Control\{
    Server,
    Server\Command,
    Servers\Remote,
};

final class Ssh implements Ping
{
    private $server;

    public function __construct(Server $server)
    {
        $this->server = $server;
    }

    public function __invoke(Neighbour $neighbour, string ...$tags): void
    {
        $server = new Remote(
            $this->server,
            $neighbour->url()->authority()->userInformation()->user(),
            $neighbour->url()->authority()->host(),
            $neighbour->url()->authority()->port()
        );
        $server
            ->processes()
            ->execute(
                Command::background('tower')
                    ->withArgument('trigger')
                    ->withOption('tags', implode(',', $tags))
                    ->withWorkingDirectory((string) $neighbour->url()->path())
            );
    }
}
