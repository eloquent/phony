<?php

declare(strict_types=1);

namespace Eloquent\Phony\Spy;

use Eloquent\Phony\Call\Call;
use Eloquent\Phony\Call\Event\CallEventFactory;
use InvalidArgumentException;
use Traversable;

/**
 * Creates iterable spies.
 */
class IterableSpyFactory
{
    /**
     * Get the static instance of this class.
     *
     * @return self The static instance.
     */
    public static function instance(): self
    {
        if (!self::$instance) {
            self::$instance = new self(CallEventFactory::instance());
        }

        return self::$instance;
    }

    /**
     * Construct a new iterable spy factory.
     *
     * @param CallEventFactory $callEventFactory The call event factory to use.
     */
    public function __construct(CallEventFactory $callEventFactory)
    {
        $this->callEventFactory = $callEventFactory;
    }

    /**
     * Create a new iterable spy.
     *
     * @param Call  $call     The call from which the iterable originated.
     * @param mixed $iterable The iterable.
     *
     * @return IterableSpy              The newly created iterable spy.
     * @throws InvalidArgumentException If the supplied iterable is invalid.
     */
    public function create(Call $call, $iterable): IterableSpy
    {
        if ($iterable instanceof Traversable) {
            return new TraversableSpy(
                $call,
                $iterable,
                $this->callEventFactory
            );
        }

        if (is_array($iterable)) {
            return new ArraySpy($call, $iterable, $this->callEventFactory);
        }

        if (is_object($iterable)) {
            $type = var_export(get_class($iterable), true);
        } else {
            $type = gettype($iterable);
        }

        throw new InvalidArgumentException(
            sprintf('Unsupported iterable of type %s.', $type)
        );
    }

    /**
     * @var ?self
     */
    private static $instance;

    /**
     * @var CallEventFactory
     */
    private $callEventFactory;
}
