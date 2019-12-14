<?php

declare(strict_types=1);

namespace Eloquent\Phony\Event\Exception;

use Exception;
use Throwable;

/**
 * No event is defined for the requested index.
 */
final class UndefinedEventException extends Exception
{
    /**
     * Construct a new undefined event exception.
     *
     * @param int        $index The index.
     * @param ?Throwable $cause The cause, if available.
     */
    public function __construct(int $index, Throwable $cause = null)
    {
        $this->index = $index;

        parent::__construct(
            sprintf('No event defined for index %d.', $index),
            0,
            $cause
        );
    }

    /**
     * Get the index.
     *
     * @return int The index.
     */
    public function index(): int
    {
        return $this->index;
    }

    /**
     * @var int
     */
    private $index;
}
