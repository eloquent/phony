<?php

declare(strict_types=1);

namespace Eloquent\Phony\Call\Exception;

use Exception;

/**
 * Thrown when an argument that was requested by name does not exist.
 */
final class UndefinedNamedArgumentException extends Exception implements
    UndefinedArgumentException
{
    /**
     * Construct a new undefined named argument exception.
     *
     * @param string $name The name.
     */
    public function __construct(string $name)
    {
        $this->name = $name;

        parent::__construct(
            sprintf(
                'No named argument defined for name %s.',
                var_export($name, true)
            )
        );
    }

    /**
     * Get the name.
     *
     * @return string The name.
     */
    public function name(): string
    {
        return $this->name;
    }

    /**
     * Returns true if the undefined argument was requested by name.
     *
     * @return bool True if the undefined argument was requested by name.
     */
    public function isNamed(): bool
    {
        return true;
    }

    /**
     * @var string
     */
    private $name;
}
