<?php
declare(strict_types = 1);

namespace Innmind\Tower\Command;

use Innmind\Tower\{
    Ping as ServerPing,
    Configuration,
    Neighbour,
};
use Innmind\CLI\{
    Command,
    Command\Arguments,
    Command\Options,
    Environment,
};
use Innmind\Immutable\{
    SetInterface,
    Set,
    Str,
};

final class Ping implements Command
{
    private $configuration;
    private $ping;

    public function __construct(Configuration $configuration, ServerPing $ping)
    {
        $this->configuration = $configuration;
        $this->ping = $ping;
    }

    public function __invoke(Environment $env, Arguments $arguments, Options $options): void
    {
        $name = $arguments->get('server');
        $neighbour = $this
            ->configuration
            ->neighbours()
            ->filter(static function(Neighbour $neighbour) use ($name): bool {
                return (string) $neighbour->name() === $name;
            })
            ->current();

        $tags = [];

        if ($options->contains('tags')) {
            $tags = Str::of($options->get('tags'))
                ->split(',')
                ->reduce(
                    Set::of('string'),
                    static function(SetInterface $tags, Str $tag): SetInterface {
                        return $tags->add((string) $tag->trim());
                    }
                );
        }

        ($this->ping)($neighbour, ...$tags);
    }

    public function __toString(): string
    {
        return <<<USAGE
ping server --tags=

Send a ping to a configured server in order to trigger its behaviour

The ping will propagate to all the neighbours of the server. In case you
provide the "tags" option (comma separated list) only the neighbours flagged
with one the tgas will be pinged.
USAGE;
    }
}
