<?php
declare(strict_types = 1);

namespace Innmind\Tower\Ping;

use Innmind\Tower\{
    Ping,
    Neighbour,
    Exception\SchemeNotSupported,
};
use Innmind\Immutable\Map;

final class Delegate implements Ping
{
    private Map $pings;

    public function __construct(Map $pings)
    {
        if (
            (string) $pings->keyType() !== 'string' ||
            (string) $pings->valueType() !== Ping::class
        ) {
            throw new \TypeError(sprintf(
                'Argument 1 must be of type Map<string, %s>',
                Ping::class
            ));
        }

        $this->pings = $pings;
    }

    public function __invoke(Neighbour $neighbour, string ...$tags): void
    {
        $scheme = $neighbour->url()->scheme()->toString();

        if (!$this->pings->contains($scheme)) {
            throw new SchemeNotSupported($scheme);
        }

        $this->pings->get($scheme)($neighbour, ...$tags);
    }
}
