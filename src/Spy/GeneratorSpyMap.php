<?php

declare(strict_types=1);

namespace Eloquent\Phony\Spy;

use Generator;
use WeakMap;

/**
 * Maps generator spies to the original generator.
 */
class GeneratorSpyMap
{
    /**
     * Construct a new generator spy map.
     */
    public function __construct()
    {
        $this->mapping = new WeakMap();
    }

    /**
     * Associate a generator spy with the generator it spies on.
     *
     * @param Generator<mixed> $spy       The generator spy.
     * @param Generator<mixed> $generator The generator.
     */
    public function set(Generator $spy, Generator $generator): void
    {
        $this->mapping->offsetSet($spy, $generator);
    }

    /**
     * Return the generator being spied on by the supplied generator spy.
     *
     * @param Generator<mixed> $spy The generator to check.
     *
     * @return ?Generator<mixed> The generator, or null if the supplied generator is not a spy.
     */
    public function get(Generator $spy): ?Generator
    {
        if ($this->mapping->offsetExists($spy)) {
            return $this->mapping->offsetGet($spy);
        }

        return null;
    }

    /**
     * @var WeakMap<Generator<mixed>,Generator<mixed>>
     */
    private $mapping;
}
