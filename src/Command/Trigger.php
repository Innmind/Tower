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
    SetInterface,
    Set,
    Str,
};

final class Trigger implements Command
{
    private $run;

    public function __construct(Run $run)
    {
        $this->run = $run;
    }

    public function __invoke(Environment $env, Arguments $arguments, Options $options): void
    {
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

        ($this->run)(...$tags);
    }

    public function __toString(): string
    {
        return <<<USAGE
trigger --tags=

Will call the actions configured to happen when this server is pinged

The "tags" controls the neighbours (having one of the tags) to be pinged.
Specified tags are forwarded to each neighbours when pinged.
USAGE;
    }
}
