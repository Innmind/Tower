<?php
declare(strict_types = 1);

namespace Innmind\Tower\Ping;

use Innmind\Tower\{
    Ping,
    Neighbour,
};
use Innmind\Socket\Internet\Transport;
use Innmind\OperatingSystem\Remote;
use Innmind\Immutable\Str;

final class Tcp implements Ping
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
            ->socket(
                Transport::tcp(),
                $neighbour->url()->authority()
            )
            ->write(Str::of(json_encode([
                'tags' => $tags,
            ])))
            ->close();
    }
}
