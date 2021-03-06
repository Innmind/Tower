<?php
declare(strict_types = 1);

namespace Innmind\Tower;

use Innmind\Tower\Exception\ActionFailed;
use Innmind\Server\Control\{
    Server,
    Server\Command,
};
use Innmind\Immutable\{
    Set,
    Str,
};

final class Run
{
    private Server\Processes $processes;
    private Configuration $configuration;
    private Ping $ping;

    public function __construct(
        Server $server,
        Configuration $configuration,
        Ping $ping
    ) {
        $this->processes = $server->processes();
        $this->configuration = $configuration;
        $this->ping = $ping;
    }

    public function __invoke(string ...$tags): void
    {
        $envs = $this->envs();
        $this
            ->configuration
            ->actions()
            ->foreach(function(string $action) use ($envs): void {
                $command = $envs->reduce(
                    Command::foreground($action),
                    static function(Command $command, EnvironmentVariable $env): Command {
                        return $command->withEnvironment(
                            $env->name(),
                            $env->value(),
                        );
                    },
                );
                $process = $this->processes->execute($command);
                $process->wait();

                if (!$process->exitCode()->successful()) {
                    throw new ActionFailed($action, $process);
                }
            });
        $this
            ->configuration
            ->neighbours()
            ->filter(static function(Neighbour $neighbour) use ($tags): bool {
                return $neighbour->matches(...$tags);
            })
            ->foreach(function(Neighbour $neighbour) use ($tags): void {
                ($this->ping)($neighbour, ...$tags);
            });
    }

    /**
     * @return Set<EnvironmentVariable>
     */
    private function envs(): Set
    {
        /** @var Set<EnvironmentVariable> */
        return $this->configuration->exports()->reduce(
            Set::of(EnvironmentVariable::class),
            function(Set $envs, string $command): Set {
                /** @var Set<EnvironmentVariable> $envs */
                $command = $envs->reduce(
                    Command::foreground($command),
                    static function(Command $command, EnvironmentVariable $env): Command {
                        return $command->withEnvironment(
                            $env->name(),
                            $env->value(),
                        );
                    },
                );
                $process = $this->processes->execute($command);
                $process->wait();
                $output = $process->output()->toString();

                return ($envs)(new EnvironmentVariable(
                    Str::of($output)->trim()->toString(),
                ));
            },
        );
    }
}
