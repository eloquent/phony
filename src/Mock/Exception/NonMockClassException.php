<?php

declare(strict_types=1);

namespace Eloquent\Phony\Mock\Exception;

use Exception;
use Throwable;

/**
 * The supplied class is not a mock class.
 */
final class NonMockClassException extends Exception implements
    MockException
{
    /**
     * Construct a non-mock class exception.
     *
     * @param string     $className The class name.
     * @param ?Throwable $cause     The cause, if available.
     */
    public function __construct(string $className, Throwable $cause = null)
    {
        $this->className = $className;

        parent::__construct(
            sprintf(
                'The class %s is not a mock class.',
                var_export($className, true)
            ),
            0,
            $cause
        );
    }

    /**
     * Get the class name.
     *
     * @return string The class name.
     */
    public function className(): string
    {
        return $this->className;
    }

    /**
     * @var string
     */
    private $className;
}
