<?php
declare(strict_types = 1);

namespace Tests\Innmind\Tower\Configuration;

use Innmind\Tower\{
    Configuration\Yaml,
    Configuration\Loader,
    Configuration,
};
use Innmind\Url\Path;
use PHPUnit\Framework\TestCase;

class YamlTest extends TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(Loader::class, new Yaml);
    }

    public function testInvoke()
    {
        $config = (new Yaml)(new Path('config/config.yml.dist'));

        $this->assertInstanceOf(Configuration::class, $config);
        $this->assertCount(1, $config->neighbours());
        $this->assertSame(
            'ssh://example.com:80/path/to/config/on/neighbour/server.yml',
            (string) $config->neighbours()->get('_name_')->url()
        );
        $this->assertSame(['foo', 'bar'], $config->neighbours()->get('_name_')->tags()->toPrimitive());
        $this->assertCount(1, $config->exports());
        $this->assertSame('ENV', $config->exports()->current()->name());
        $this->assertSame('value', $config->exports()->current()->value());
        $this->assertCount(1, $config->actions());
        $this->assertSame('some bash command', $config->actions()->current());
    }
}
