<?php
declare(strict_types = 1);

namespace Innmind\Tower;

use Innmind\Tower\Exception\DomainException;
use Innmind\Immutable\Str;

final class EnvironmentVariable
{
    private const PATTERN = '~^(?<name>[A-Z0-9_]+)=(?<value>.*)$~';

    private string $name;
    private string $value;

    public function __construct(string $value)
    {
        $value = Str::of($value);

        if (!$value->matches(self::PATTERN)) {
            throw new DomainException($value->toString());
        }

        $parts = $value->capture(self::PATTERN);

        $this->name = $parts->get('name')->toString();
        $this->value = $parts->get('value')->toString();
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
