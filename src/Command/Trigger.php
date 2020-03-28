<?php
declare(strict_types = 1);

namespace Innmind\Tower\Command;

use Innmind\Tower\Run;
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
use function Innmind\Immutable\unwrap;

final class Trigger implements Command
{
    private Run $run;

    public function __construct(Run $run)
    {
        $this->run = $run;
    }

    public function __invoke(Environment $env, Arguments $arguments, Options $options): void
    {
        $tags = [];

        if ($options->contains('tags')) {
            $tags = unwrap(Str::of($options->get('tags'))
                ->split(',')
                ->reduce(
                    Set::of('string'),
                    static function(Set $tags, Str $tag): Set {
                        return $tags->add($tag->trim()->toString());
                    },
                ));
        }

        ($this->run)(...$tags);
    }

    public function toString(): string
    {
        return <<<USAGE
trigger --tags=

Will call the actions configured to happen when this server is pinged

The "tags" controls the neighbours (having one of the tags) to be pinged.
Specified tags are forwarded to each neighbours when pinged.
USAGE;
    }
}
