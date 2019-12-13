<?php

declare(strict_types=1);

namespace Eloquent\Phony\Mock\Exception;

use Exception;

/**
 * The class is already defined.
 */
final class ClassExistsException extends Exception implements
    MockException
{
    /**
     * Construct a class exists exception.
     *
     * @param string $className The class name.
     */
    public function __construct(string $className)
    {
        $this->className = $className;

        parent::__construct(
            sprintf(
                'Class %s is already defined.',
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
