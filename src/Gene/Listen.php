<?php
declare(strict_types = 1);

namespace Innmind\Tower\Gene;

use Innmind\Genome\{
    Gene,
    History,
    Exception\PreConditionFailed,
    Exception\ExpressionFailed,
};
use Innmind\OperatingSystem\OperatingSystem;
use Innmind\Server\Control\{
    Server,
    Server\Command,
    Server\Script,
    Exception\ScriptFailed,
};

final class Listen implements Gene
{
    public function name(): string
    {
        return 'Tower listen';
    }

    public function express(
        OperatingSystem $local,
        Server $target,
        History $history
    ): History {
        try {
            $preCondition = new Script(
                Command::foreground('composer')
                    ->withArgument('global')
                    ->withArgument('exec')
                    ->withArgument('tower help')
                    ->withShortOption('v'),
            );
            $preCondition($target);
        } catch (ScriptFailed $e) {
            throw new PreConditionFailed('tower is missing');
        }

        try {
            $listen = new Script(
                Command::foreground('composer')
                    ->withArgument('global')
                    ->withArgument('exec')
                    ->withArgument('tower listen 1337 --daemon')
                    ->withShortOption('v'),
            );
            $listen($target);
        } catch (ScriptFailed $e) {
            throw new ExpressionFailed($this->name());
        }

        return $history;
    }
}
