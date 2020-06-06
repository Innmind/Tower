<?php
declare(strict_types = 1);

namespace Tests\Innmind\Tower;

use Innmind\Tower\{
    EnvironmentVariable,
    Exception\DomainException,
};
use PHPUnit\Framework\TestCase;
use Innmind\BlackBox\{
    PHPUnit\BlackBox,
    Set,
};

class EnvironmentVariableTest extends TestCase
{
    use BlackBox;

    public function testInterface()
    {
        $this
            ->forAll(Set\Unicode::strings())
            ->filter(fn($string) => \strpos($string, "\n") === false)
            ->then(function(string $string): void {
                $env = new EnvironmentVariable('FOO_BAR_42='.$string);

                $this->assertSame('FOO_BAR_42', $env->name());
                $this->assertSame($string, $env->value());
            });
    }

    public function testThrowWhenInvalidName()
    {
        $this
            ->forAll(Set\Unicode::strings())
            ->then(function(string $string): void {
                $this->expectException(DomainException::class);
                $this->expectExceptionMessage($string.'=foo');

                new EnvironmentVariable($string.'=foo');
            });
    }
}
