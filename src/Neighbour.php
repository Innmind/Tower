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
    private Name $name;
    private UrlInterface $url;
    private Set $tags;

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

    public function matches(string ...$tags): bool
    {
        $tags = Set::of('string', ...$tags);

        if ($tags->size() === 0) {
            return true;
        }

        return $this->tags->intersect($tags)->size() > 0;
    }
}
