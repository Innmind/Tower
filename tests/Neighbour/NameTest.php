<?php
declare(strict_types = 1);

namespace Tests\Innmind\Tower\Neighbour;

use Innmind\Tower\{
    Neighbour\Name,
    Exception\DomainException,
};
use PHPUnit\Framework\TestCase;
use Eris\{
    TestTrait,
    Generator,
};

class NameTest extends TestCase
{
    use TestTrait;

    public function testInterface()
    {
        $this
            ->forAll(Generator\string())
            ->when(static function(string $string): bool {
                return $string !== '';
            })
            ->then(function(string $string): void {
                $this->assertSame($string, (new Name($string))->toString());
            });
    }

    public function testThrowWhenEmptyName()
    {
        $this->expectException(DomainException::class);

        new Name('');
    }
}
