<?php
declare(strict_types = 1);

namespace Tests\Innmind\Tower;

use Innmind\Tower\{
    EnvironmentVariable,
    Exception\DomainException,
};
use PHPUnit\Framework\TestCase;
use Eris\{
    TestTrait,
    Generator,
};

class EnvironmentVariableTest extends TestCase
{
    use TestTrait;

    public function testInterface()
    {
        $this
            ->forAll(Generator\string())
            ->then(function(string $string): void {
                $env = new EnvironmentVariable('FOO_BAR_42='.$string);

                $this->assertSame('FOO_BAR_42', $env->name());
                $this->assertSame($string, $env->value());
            });
    }

    public function testThrowWhenInvalidName()
    {
        $this
            ->forAll(Generator\string())
            ->then(function(string $string): void {
                $this->expectException(DomainException::class);
                $this->expectExceptionMessage($string.'=foo');

                new EnvironmentVariable($string.'=foo');
            });
    }
}
