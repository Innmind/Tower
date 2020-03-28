<?php
declare(strict_types = 1);

namespace Innmind\Tower\Listener;

use Innmind\Tower\Run;
use Innmind\Socket\Event\ConnectionReady;
use Innmind\Json\Json;

final class Ping
{
    private Run $run;

    public function __construct(Run $run)
    {
        $this->run = $run;
    }

    public function __invoke(ConnectionReady $event): void
    {
        /** @var array{tags?: list<string>}|mixed */
        $payload = Json::decode($event->connection()->read()->toString());

        if (!\is_array($payload)) {
            return;
        }

        if (!isset($payload['tags'])) {
            return;
        }

        if (!\is_array($payload['tags'])) {
            return;
        }

        /** @psalm-suppress MixedArgument */
        ($this->run)(...$payload['tags']);
    }
}
