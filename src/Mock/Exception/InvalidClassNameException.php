<?php

declare(strict_types=1);

namespace Eloquent\Phony\Mock\Exception;

use Exception;

/**
 * The supplied class name is invalid.
 */
final class InvalidClassNameException extends Exception implements
    MockException
{
    /**
     * Construct a new invalid class name exception.
     *
     * @param mixed $className The class name.
     */
    public function __construct($className)
    {
        $this->className = $className;

        parent::__construct(
            sprintf('Invalid class name %s.', var_export($className, true))
        );
    }

    /**
     * Get the class name.
     *
     * @return mixed The class name.
     */
    public function className()
    {
        return $this->className;
    }

    /**
     * @var mixed
     */
    private $className;
}
