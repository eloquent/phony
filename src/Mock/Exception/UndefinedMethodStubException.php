<?php

declare(strict_types=1);

namespace Eloquent\Phony\Mock\Exception;

use Exception;

/**
 * The requested method stub does not exist.
 */
final class UndefinedMethodStubException extends Exception implements
    MockException
{
    /**
     * Construct a new undefined method stub exception.
     *
     * @param string $className The class name.
     * @param string $name      The method name.
     */
    public function __construct(string $className, string $name)
    {
        $this->className = $className;
        $this->name = $name;

        parent::__construct(
            sprintf(
                'The requested method stub %s::%s() does not exist.',
                $className,
                $name
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
     * Get the method name.
     *
     * @return string The method name.
     */
    public function name(): string
    {
        return $this->name;
    }

    /**
     * @var string
     */
    private $className;

    /**
     * @var string
     */
    private $name;
}
