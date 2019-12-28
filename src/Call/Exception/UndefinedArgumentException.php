<?php

declare(strict_types=1);

namespace Eloquent\Phony\Call\Exception;

use Exception;

/**
 * Thrown when an argument that was requested by index does not exist.
 */
final class UndefinedArgumentException extends Exception
{
    /**
     * Construct a new undefined argument exception.
     *
     * @param int $index The index.
     */
    public function __construct(int $index)
    {
        $this->index = $index;

        parent::__construct(
            sprintf(
                'No argument defined for index %s.',
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
     * @var int
     */
    private $index;
}
