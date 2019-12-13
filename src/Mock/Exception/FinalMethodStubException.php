<?php

declare(strict_types=1);

namespace Eloquent\Phony\Mock\Exception;

use Exception;

/**
 * The method cannot be stubbed because it is final.
 */
final class FinalMethodStubException extends Exception implements
    MockException
{
    /**
     * Construct a new final method stub exception.
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
                'The method %s::%s() cannot be stubbed because it is final.',
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
