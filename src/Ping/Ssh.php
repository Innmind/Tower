<?php
declare(strict_types = 1);

namespace Innmind\Tower\Ping;

use Innmind\Tower\{
    Ping,
    Neighbour,
};
use Innmind\OperatingSystem\Remote;
use Innmind\Server\Control\Server\Command;

final class Ssh implements Ping
{
    private $remote;

    public function __construct(Remote $remote)
    {
        $this->remote = $remote;
    }

    public function __invoke(Neighbour $neighbour, string ...$tags): void
    {
        $this
            ->remote
            ->ssh($neighbour->url())
            ->processes()
            ->execute(
                Command::background('tower')
                    ->withArgument('trigger')
                    ->withOption('tags', implode(',', $tags))
                    ->withWorkingDirectory((string) $neighbour->url()->path())
            );
    }
}
