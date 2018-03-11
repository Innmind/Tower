<?php
declare(strict_types = 1);

namespace Innmind\Tower;

use Innmind\Tower\Exception\DomainException;
use Innmind\Immutable\Str;

final class EnvironmentVariable
{
    private const PATTERN = '~^(?<name>[A-Z0-9_]+)=(?<value>.*)$~';

    private $name;
    private $value;

    public function __construct(string $value)
    {
        $value = Str::of($value);

        if (!$value->matches(self::PATTERN)) {
            throw new DomainException((string) $value);
        }

        $parts = $value->capture(self::PATTERN);

        $this->name = (string) $parts->get('name');
        $this->value = (string) $parts->get('value');
    }

    public function name(): string
    {
        return $this->name;
    }

    public function value(): string
    {
        return $this->value;
    }
}
