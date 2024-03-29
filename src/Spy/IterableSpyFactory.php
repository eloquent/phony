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
     * @var CallEventFactory
     */
    private $callEventFactory;
}
