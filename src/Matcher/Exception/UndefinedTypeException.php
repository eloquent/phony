<?php

declare(strict_types=1);

namespace Eloquent\Phony\Matcher\Exception;

use Exception;

/**
 * Thrown when the type specified in a matcher is not defined.
 */
final class UndefinedTypeException extends Exception
{
    /**
     * Construct a new undefined type exception.
     *
     * @param string $type The type.
     */
    public function __construct(string $type)
    {
        $this->type = $type;

        parent::__construct(
            sprintf('Undefined type %s.', var_export($type, true))
        );
    }

    /**
     * Get the type.
     *
     * @return string The type.
     */
    public function type(): string
    {
        return $this->type;
    }

    /**
     * @var string
     */
    private $type;
}
