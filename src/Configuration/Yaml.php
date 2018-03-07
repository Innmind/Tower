<?php
declare(strict_types = 1);

namespace Innmind\Tower\Configuration;

use Innmind\Tower\{
    Configuration,
    Neighbour,
    Neighbour\Name,
    EnvironmentVariable,
};
use Innmind\Url\{
    Url,
    PathInterface,
};
use Innmind\Immutable\{
    Sequence,
    Map,
    Set,
};
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Yaml\Yaml as Parser;

final class Yaml implements Loader
{
    private $processor;
    private $config;

    public function __construct()
    {
        $this->processor = new Processor;
        $this->config = new Schema;
    }

    public function __invoke(PathInterface $configPath): Configuration
    {
        $config = $this->processor->processConfiguration(
            $this->config,
            [Parser::parseFile((string) $configPath)]
        );
        $neighbours = new Map('string', Neighbour::class);

        foreach ($config['neighbours'] as $name => $value) {
            $neighbours = $neighbours->put(
                $name,
                new Neighbour(
                    new Name($name),
                    Url::fromString($value['url']),
                    ...$value['tags']
                )
            );
        }


        return new Configuration(
            $neighbours,
            Sequence::of(...$config['exports'])->reduce(
                Set::of(EnvironmentVariable::class),
                static function(Set $exports, string $env): Set {
                    return $exports->add(new EnvironmentVariable($env));
                }
            ),
            Set::of('string', ...$config['actions'])
        );
    }
}
