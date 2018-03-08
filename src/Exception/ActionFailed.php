<?php
declare(strict_types = 1);

namespace Innmind\Tower\Exception;

use Innmind\Server\Control\Server\Process;

final class ActionFailed extends RuntimeException
{
    private $process;

    public function __construct(string $action, Process $process)
    {
        parent::__construct($action);
        $this->process = $process;
    }

    public function process(): Process
    {
        return $this->process;
    }
}
