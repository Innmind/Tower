<?php
declare(strict_types = 1);

namespace Tests\Innmind\Tower\Configuration;

use Innmind\Tower\{
    Configuration\Yaml,
    Configuration\Loader,
    Configuration,
};
use Innmind\Url\Path;
use function Innmind\Immutable\{
    first,
    unwrap,
};
use PHPUnit\Framework\TestCase;

class YamlTest extends TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(Loader::class, new Yaml);
    }

    public function testInvoke()
    {
        $config = (new Yaml)(Path::of('config/config.yml.dist'));

        $this->assertInstanceOf(Configuration::class, $config);
        $this->assertCount(1, $config->neighbours());
        $this->assertSame(
            '_name_',
            first($config->neighbours())->name()->toString()
        );
        $this->assertSame(
            'ssh://example.com:80/path/to/config/on/neighbour/server.yml',
            first($config->neighbours())->url()->toString()
        );
        $this->assertSame(['foo', 'bar'], unwrap(first($config->neighbours())->tags()));
        $this->assertCount(1, $config->exports());
        $this->assertSame('echo "ENV=value"', first($config->exports()));
        $this->assertCount(1, $config->actions());
        $this->assertSame('some bash command', first($config->actions()));
    }
}
