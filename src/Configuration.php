<?php
declare(strict_types = 1);

namespace Innmind\Tower;

use Innmind\Tower\Configuration\Loader;
use Innmind\Immutable\Set;
use function Innmind\Immutable\assertSet;

final class Configuration
{
    /** @var Set<Neighbour> */
    private Set $neighbours;
    /** @var Set<string> */
    private Set $exports;
    /** @var Set<string> */
    private Set $actions;

    /**
     * @param Set<Neighbour> $neighbours
     * @param Set<string> $exports
     * @param Set<string> $actions
     */
    public function __construct(
        Set $neighbours,
        Set $exports,
        Set $actions
    ) {
        assertSet(Neighbour::class, $neighbours, 1);
        assertSet('string', $exports, 2);
        assertSet('string', $actions, 3);

        $this->neighbours = $neighbours;
        $this->exports = $exports;
        $this->actions = $actions;
    }

    /**
     * @return Set<Neighbour>
     */
    public function neighbours(): Set
    {
        return $this->neighbours;
    }

    /**
     * @return Set<string>
     */
    public function exports(): Set
    {
        return $this->exports;
    }

    /**
     * @return Set<string>
     */
    public function actions(): Set
    {
        return $this->actions;
    }
}
