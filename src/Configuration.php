<?php
declare(strict_types = 1);

namespace Innmind\Tower;

use Innmind\Tower\Configuration\Loader;
use Innmind\Url\PathInterface;
use Innmind\Immutable\SetInterface;

final class Configuration
{
    private $neighbours;
    private $exports;
    private $actions;

    public function __construct(
        SetInterface $neighbours,
        SetInterface $exports,
        SetInterface $actions
    ) {
        if ((string) $neighbours->type() !== Neighbour::class) {
            throw new \TypeError(sprintf(
                'Argument 1 must be of type SetInterface<%s>',
                Neighbour::class
            ));
        }

        if ((string) $exports->type() !== 'string') {
            throw new \TypeError('Argument 2 must be of type SetInterface<string>');
        }

        if ((string) $actions->type() !== 'string') {
            throw new \TypeError('Argument 3 must be of type SetInterface<string>');
        }

        $this->neighbours = $neighbours;
        $this->exports = $exports;
        $this->actions = $actions;
    }

    /**
     * @return SetInterface<Neighbour>
     */
    public function neighbours(): SetInterface
    {
        return $this->neighbours;
    }

    /**
     * @return SetInterface<string>
     */
    public function exports(): SetInterface
    {
        return $this->exports;
    }

    /**
     * @return SetInterface<string>
     */
    public function actions(): SetInterface
    {
        return $this->actions;
    }
}
