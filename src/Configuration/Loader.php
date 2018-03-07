<?php
declare(strict_types = 1);

namespace Innmind\Tower\Configuration;

use Innmind\Tower\Configuration;
use Innmind\Url\PathInterface;

interface Loader
{
    public function __invoke(PathInterface $configPath): Configuration;
}
