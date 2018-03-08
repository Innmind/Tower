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
    private $processes;
    private $configuration;

    public function __construct(Server $server, Configuration $configuration)
    {
        $this->processes = $server->processes();
        $this->configuration = $configuration;
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
                            $env->value()
                        );
                    }
                );
                $process = $this->processes->execute($command)->wait();

                if (!$process->exitCode()->isSuccessful()) {
                    throw new ActionFailed($action, $process);
                }
            });
        $this
            ->configuration
            ->neighbours()
            ->filter(static function(Neighbour $neighbour) use ($tags): bool {
                return $neighbour->matches(...$tags);
            })
            ->foreach(function(Neighbour $neighbour): void {
                //todo ping the neighbour
            });
    }

    /**
     * @return Set<EnvironmentVariable>
     */
    private function envs(): Set
    {
        return $this->configuration->exports()->reduce(
            Set::of(EnvironmentVariable::class),
            function(Set $envs, string $command): Set {
                $command = $envs->reduce(
                    Command::foreground($command),
                    static function(Command $command, EnvironmentVariable $env): Command {
                        return $command->withEnvironment(
                            $env->name(),
                            $env->value()
                        );
                    }
                );
                $output = (string) $this
                    ->processes
                    ->execute($command)
                    ->wait()
                    ->output();

                return $envs->add(new EnvironmentVariable(
                    (string) Str::of($output)->trim()
                ));
            }
        );
    }
}
