<?php
declare(strict_types = 1);

namespace Innmind\Tower;

use Innmind\Tower\Neighbour\Name;
use Innmind\Url\UrlInterface;
use Innmind\Immutable\{
    SetInterface,
    Set,
};

final class Neighbour
{
    private $name;
    private $url;
    private $tags;

    public function __construct(Name $name, UrlInterface $url, string ...$tags)
    {
        $this->name = $name;
        $this->url = $url;
        $this->tags = Set::of('string', ...$tags);
    }

    public function name(): Name
    {
        return $this->name;
    }

    public function url(): UrlInterface
    {
        return $this->url;
    }

    /**
     * @return SetInterface<string>
     */
    public function tags(): SetInterface
    {
        return $this->tags;
    }
}
