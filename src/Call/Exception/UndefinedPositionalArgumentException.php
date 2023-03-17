<?php

declare(strict_types=1);

namespace Eloquent\Phony\Call\Exception;

use Exception;

/**
 * Thrown when an argument that was requested by position does not exist.
 */
final class UndefinedPositionalArgumentException extends Exception implements
    UndefinedArgumentException
{
    /**
     * Construct a new undefined positional argument exception.
     *
     * @param int $position The position.
     */
    public function __construct(int $position)
    {
        $this->position = $position;

        parent::__construct(
            sprintf(
                'No positional argument defined for position %s.',
                var_export($position, true)
            )
        );
    }

    /**
     * Get the position.
     *
     * @return int The position.
     */
    public function position(): int
    {
        return $this->position;
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
    private $position;
}
