<?php
declare(strict_types = 1);

namespace Innmind\Tower\Listener;

use Innmind\Tower\Run;
use Innmind\Socket\Event\DataReceived;

final class Ping
{
    private $run;

    public function __construct(Run $run)
    {
        $this->run = $run;
    }

    public function __invoke(DataReceived $event): void
    {
        $payload = json_decode((string) $event->data(), true);

        if (!\is_array($payload)) {
            return;
        }

        if (!isset($payload['tags'])) {
            return;
        }

        if (!\is_array($payload['tags'])) {
            return;
        }

        ($this->run)(...$payload['tags']);
    }
}
