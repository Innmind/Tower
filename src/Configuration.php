<?php
declare(strict_types = 1);

namespace Innmind\Tower;

use Innmind\Tower\Configuration\Loader;
use Innmind\Url\PathInterface;
use Innmind\Immutable\{
    MapInterface,
    SetInterface,
};

final class Configuration
{
    private $neighbours;
    private $exports;
    private $actions;

    public function __construct(
        MapInterface $neighbours,
        SetInterface $exports,
        SetInterface $actions
    ) {
        if (
            (string) $neighbours->keyType() !== 'string' ||
            (string) $neighbours->valueType() !== Neighbour::class
        ) {
            throw new \TypeError(sprintf(
                'Argument 1 must be of type MapInterface<string, %s>',
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

    public static function load(PathInterface $path, Loader $load): self
    {
        return $load($path);
    }

    /**
     * @return MapInterface<string, Neighbour>
     */
    public function neighbours(): MapInterface
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
