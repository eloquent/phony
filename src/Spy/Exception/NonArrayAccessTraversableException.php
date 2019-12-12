<?php

declare(strict_types=1);

namespace Eloquent\Phony\Spy\Exception;

use Exception;
use Traversable;

/**
 * The wrapped traversable object does not implement ArrayAccess.
 */
final class NonArrayAccessTraversableException extends Exception
{
    /**
     * Construct a non-array-access traversable exception.
     *
     * @param Traversable $traversable The traversable.
     */
    public function __construct(Traversable $traversable)
    {
        $this->traversable = $traversable;

        parent::__construct(
            sprintf(
                'Unable to use array acces on a traversable object of type %s,' .
                    ' since it does not implement ArrayAccess.',
                var_export(get_class($traversable), true)
            )
        );
    }

    /**
     * Get the traversable.
     *
     * @return Traversable The traversable.
     */
    public function traversable(): Traversable
    {
        return $this->traversable;
    }

    private $traversable;
}
