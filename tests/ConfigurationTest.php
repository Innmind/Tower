<?php
declare(strict_types = 1);

namespace Tests\Innmind\Tower;

use Innmind\Tower\{
    Configuration,
    Configuration\Loader,
    Neighbour,
    EnvironmentVariable,
};
use Innmind\Url\PathInterface;
use Innmind\Immutable\Set;
use PHPUnit\Framework\TestCase;

class ConfigurationTest extends TestCase
{
    public function testInterface()
    {
        $conf = new Configuration(
            $neighbours = Set::of(Neighbour::class),
            $env = Set::of('string'),
            $actions = Set::of('string')
        );

        $this->assertSame($neighbours, $conf->neighbours());
        $this->assertSame($env, $conf->exports());
        $this->assertSame($actions, $conf->actions());
    }

    public function testThrowWhenInvalidNeighbour()
    {
        $this->expectException(\TypeError::class);
        $this->expectExceptionMessage('Argument 1 must be of type SetInterface<Innmind\Tower\Neighbour>');

        new Configuration(
            Set::of('int'),
            Set::of('string'),
            Set::of('string')
        );
    }

    public function testThrowWhenInvalidEnvVars()
    {
        $this->expectException(\TypeError::class);
        $this->expectExceptionMessage('Argument 2 must be of type SetInterface<string>');

        new Configuration(
            Set::of(Neighbour::class),
            Set::of('int'),
            Set::of('string')
        );
    }

    public function testThrowWhenInvalidActions()
    {
        $this->expectException(\TypeError::class);
        $this->expectExceptionMessage('Argument 3 must be of type SetInterface<string>');

        new Configuration(
            Set::of(Neighbour::class),
            Set::of('string'),
            Set::of('int')
        );
    }
}
