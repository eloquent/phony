<?php

declare(strict_types=1);

namespace Eloquent\Phony\Mock\Exception;

use Exception;

/**
 * An invalid definition was encountered.
 */
final class InvalidDefinitionException extends Exception implements
    MockException
{
    /**
     * Construct a new invalid definition exception.
     *
     * @param mixed $name  The name.
     * @param mixed $value The value.
     */
    public function __construct($name, $value)
    {
        $this->name = $name;
        $this->value = $value;

        parent::__construct(
            sprintf(
                'Invalid mock definition %s: (%s).',
                var_export($name, true),
                gettype($value)
            )
        );
    }

    /**
     * Get the name.
     *
     * @return mixed The name.
     */
    public function name()
    {
        return $this->name;
    }

    /**
     * Get the value.
     *
     * @return mixed The value.
     */
    public function value()
    {
        return $this->value;
    }

    /**
     * @var mixed
     */
    private $name;

    /**
     * @var mixed
     */
    private $value;
}
