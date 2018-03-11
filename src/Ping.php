<?php
declare(strict_types = 1);

namespace Innmind\Tower;

interface Ping
{
    public function __invoke(Neighbour $neighbour, string ...$tags): void;
}
