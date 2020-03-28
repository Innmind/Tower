<?php
declare(strict_types = 1);

namespace Innmind\Tower;

use Innmind\Tower\Neighbour\Name;
use Innmind\Url\Url;
use Innmind\Immutable\Set;

final class Neighbour
{
    private Name $name;
    private Url $url;
    /** @var Set<string> */
    private Set $tags;

    public function __construct(Name $name, Url $url, string ...$tags)
    {
        $this->name = $name;
        $this->url = $url;
        $this->tags = Set::strings(...$tags);
    }

    public function name(): Name
    {
        return $this->name;
    }

    public function url(): Url
    {
        return $this->url;
    }

    /**
     * @return Set<string>
     */
    public function tags(): Set
    {
        return $this->tags;
    }

    public function matches(string ...$tags): bool
    {
        $tags = Set::strings(...$tags);

        if ($tags->empty()) {
            return true;
        }

        return !$this->tags->intersect($tags)->empty();
    }
}
