<?php
declare(strict_types = 1);

namespace Innmind\Tower;

use Innmind\Tower\Configuration\Loader;
use Innmind\Immutable\Set;

final class Configuration
{
    private Set $neighbours;
    private Set $exports;
    private Set $actions;

    public function __construct(
        Set $neighbours,
        Set $exports,
        Set $actions
    ) {
        if ((string) $neighbours->type() !== Neighbour::class) {
            throw new \TypeError(sprintf(
                'Argument 1 must be of type Set<%s>',
                Neighbour::class
            ));
        }

        if ((string) $exports->type() !== 'string') {
            throw new \TypeError('Argument 2 must be of type Set<string>');
        }

        if ((string) $actions->type() !== 'string') {
            throw new \TypeError('Argument 3 must be of type Set<string>');
        }

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
