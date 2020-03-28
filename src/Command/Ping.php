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
    Set,
    Str,
};
use function Innmind\Immutable\{
    first,
    unwrap,
};

final class Ping implements Command
{
    private Configuration $configuration;
    private ServerPing $ping;

    public function __construct(Configuration $configuration, ServerPing $ping)
    {
        $this->configuration = $configuration;
        $this->ping = $ping;
    }

    public function __invoke(Environment $env, Arguments $arguments, Options $options): void
    {
        $name = $arguments->get('server');
        $neighbour = first($this
            ->configuration
            ->neighbours()
            ->filter(static function(Neighbour $neighbour) use ($name): bool {
                return $neighbour->name()->toString() === $name;
            }));

        ($this->ping)($neighbour, ...unwrap($arguments->pack()));
    }

    public function toString(): string
    {
        return <<<USAGE
ping server ...tags

Send a ping to a configured server in order to trigger its behaviour

The ping will propagate to all the neighbours of the server. In case you
provide the "tags" option (comma separated list) only the neighbours flagged
with one the tgas will be pinged.
USAGE;
    }
}
