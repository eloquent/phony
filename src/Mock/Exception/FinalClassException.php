<?php

declare(strict_types=1);

namespace Eloquent\Phony\Mock\Exception;

use Exception;

/**
 * Unable to extend final class.
 */
final class FinalClassException extends Exception implements
    MockException
{
    /**
     * Construct a final class exception.
     *
     * @param string $className The class name.
     */
    public function __construct(string $className)
    {
        $this->className = $className;

        parent::__construct(
            sprintf(
                'Unable to extend final class %s.',
                var_export($className, true)
            )
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
