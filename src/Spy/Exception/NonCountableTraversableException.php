<?php

declare(strict_types=1);

namespace Eloquent\Phony\Spy\Exception;

use Exception;
use Traversable;

/**
 * The wrapped traversable object does not implement Countable.
 */
final class NonCountableTraversableException extends Exception
{
    /**
     * Construct a non-countable traversable exception.
     *
     * @param Traversable<mixed> $traversable The traversable.
     */
    public function __construct(Traversable $traversable)
    {
        $this->traversable = $traversable;

        parent::__construct(
            sprintf(
                'Unable to count a traversable object of type %s, since it ' .
                    'does not implement Countable.',
                var_export(get_class($traversable), true)
            )
        );
    }

    /**
     * Get the traversable.
     *
     * @return Traversable<mixed> The traversable.
     */
    public function traversable(): Traversable
    {
        return $this->traversable;
    }

    /**
     * @var Traversable<mixed>
     */
    private $traversable;
}
