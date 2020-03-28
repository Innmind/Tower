<?php
declare(strict_types = 1);

namespace Innmind\Tower\Configuration;

use Innmind\Tower\Configuration;
use Innmind\Url\Path;

interface Loader
{
    public function __invoke(Path $configPath): Configuration;
}
