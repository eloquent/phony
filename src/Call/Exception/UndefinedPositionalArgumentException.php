<?php

declare(strict_types=1);

namespace Eloquent\Phony\Call\Exception;

use Exception;

/**
 * Thrown when an argument that was requested by index does not exist.
 */
final class UndefinedPositionalArgumentException extends Exception implements
    UndefinedArgumentException
{
    /**
     * Construct a new undefined positional argument exception.
     *
     * @param int $index The index.
     */
    public function __construct(int $index)
    {
        $this->index = $index;

        parent::__construct(
            sprintf(
                'No positional argument defined for index %s.',
                var_export($index, true)
            )
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
     * Returns true if the undefined argument was requested by name.
     *
     * @return bool True if the undefined argument was requested by name.
     */
    public function isNamed(): bool
    {
        return false;
    }

    /**
     * @var int
     */
    private $index;
}
