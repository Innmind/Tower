<?php
declare(strict_types = 1);

namespace Tests\Innmind\Tower;

use Innmind\Tower\{
    Configuration,
    Neighbour,
    EnvironmentVariable,
};
use Innmind\Immutable\{
    Map,
    Set,
};
use PHPUnit\Framework\TestCase;

class ConfigurationTest extends TestCase
{
    public function testInterface()
    {
        $conf = new Configuration(
            $neighbours = new Map('string', Neighbour::class),
            $env = Set::of(EnvironmentVariable::class),
            $actions = Set::of('string')
        );

        $this->assertSame($neighbours, $conf->neighbours());
        $this->assertSame($env, $conf->exports());
        $this->assertSame($actions, $conf->actions());
    }

    public function testThrowWhenInvalidNeighbourKey()
    {
        $this->expectException(\TypeError::class);
        $this->expectExceptionMessage('Argument 1 must be of type MapInterface<string, Innmind\Tower\Neighbour>');

        new Configuration(
            new Map('int', Neighbour::class),
            Set::of(EnvironmentVariable::class),
            Set::of('string')
        );
    }

    public function testThrowWhenInvalidNeighbourValue()
    {
        $this->expectException(\TypeError::class);
        $this->expectExceptionMessage('Argument 1 must be of type MapInterface<string, Innmind\Tower\Neighbour>');

        new Configuration(
            new Map('string', 'string'),
            Set::of(EnvironmentVariable::class),
            Set::of('string')
        );
    }

    public function testThrowWhenInvalidEnvVars()
    {
        $this->expectException(\TypeError::class);
        $this->expectExceptionMessage('Argument 2 must be of type SetInterface<Innmind\Tower\EnvironmentVariable>');

        new Configuration(
            new Map('string', Neighbour::class),
            Set::of('string'),
            Set::of('string')
        );
    }

    public function testThrowWhenInvalidActions()
    {
        $this->expectException(\TypeError::class);
        $this->expectExceptionMessage('Argument 3 must be of type SetInterface<string>');

        new Configuration(
            new Map('string', Neighbour::class),
            Set::of(EnvironmentVariable::class),
            Set::of('int')
        );
    }
}
