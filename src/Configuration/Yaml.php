<?php
declare(strict_types = 1);

namespace Innmind\Tower\Configuration;

use Innmind\Tower\{
    Configuration,
    Neighbour,
    Neighbour\Name,
};
use Innmind\Url\{
    Url,
    Path,
};
use Innmind\Immutable\Set;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Yaml\Yaml as Parser;

final class Yaml implements Loader
{
    private Processor $processor;
    private Schema $config;

    public function __construct()
    {
        $this->processor = new Processor;
        $this->config = new Schema;
    }

    public function __invoke(Path $configPath): Configuration
    {
        $config = $this->processor->processConfiguration(
            $this->config,
            [Parser::parseFile($configPath->toString())]
        );
        $neighbours = Set::of(Neighbour::class);

        foreach ($config['neighbours'] as $name => $value) {
            $neighbours = $neighbours->add(
                new Neighbour(
                    new Name($name),
                    Url::of($value['url']),
                    ...$value['tags']
                )
            );
        }


        return new Configuration(
            $neighbours,
            Set::of('string', ...$config['exports']),
            Set::of('string', ...$config['actions'])
        );
    }
}
