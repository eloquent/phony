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
     * Get the static instance of this class.
     *
     * @return self The static instance.
     */
    public static function instance(): self
    {
        if (!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

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
        /** @var WeakMap */
        $mapping = $this->mapping;
        $mapping->offsetSet($spy, $generator);
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
        /** @var WeakMap */
        $mapping = $this->mapping;

        if ($mapping->offsetExists($spy)) {
            return $mapping->offsetGet($spy);
        }

        return null;
    }

    /**
     * @var ?self
     */
    private static $instance;

    /**
     * @var ?WeakMap
     */
    private $mapping;
}
