<?php
declare(strict_types = 1);

namespace Innmind\Tower\Ping;

use Innmind\Tower\{
    Ping,
    Neighbour,
};
use Innmind\Socket\{
    Client\Internet,
    Internet\Transport,
};
use Innmind\Immutable\Str;

final class Tcp implements Ping
{
    public function __invoke(Neighbour $neighbour, string ...$tags): void
    {
        $client = new Internet(
            Transport::tcp(),
            $neighbour->url()->authority()
        );
        $client
            ->write(Str::of(json_encode([
                'tags' => $tags,
            ])))
            ->close();
    }
}
