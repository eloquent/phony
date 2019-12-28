<?php

declare(strict_types=1);

namespace Eloquent\Phony\Hook\Exception;

use Exception;

/**
 * The function is already defined.
 */
final class FunctionExistsException extends Exception implements
    FunctionHookException
{
    /**
     * Construct a function exists exception.
     *
     * @param string $functionName The function name.
     */
    public function __construct(string $functionName)
    {
        $this->functionName = $functionName;

        parent::__construct(
            sprintf(
                'Function %s is already defined.',
                var_export($functionName, true)
            )
        );
    }

    /**
     * Get the function name.
     *
     * @return string The function name.
     */
    public function functionName(): string
    {
        return $this->functionName;
    }

    /**
     * @var string
     */
    private $functionName;
}
