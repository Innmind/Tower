<?php
declare(strict_types = 1);

namespace Innmind\Tower\Ping;

use Innmind\Tower\{
    Ping,
    Neighbour,
    Exception\SchemeNotSupported,
};
use Innmind\Immutable\Map;
use function Innmind\Immutable\assertMap;

final class Delegate implements Ping
{
    /** @var Map<string, Ping> */
    private Map $pings;

    /**
     * @param Map<string, Ping> $pings
     */
    public function __construct(Map $pings)
    {
        assertMap('string', Ping::class, $pings, 1);

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
