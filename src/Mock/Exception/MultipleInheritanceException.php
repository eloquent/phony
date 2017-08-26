<?php

declare(strict_types=1);

namespace Eloquent\Phony\Mock\Exception;

use Exception;

/**
 * Unable to extend multiple classes.
 */
final class MultipleInheritanceException extends Exception implements
    MockException
{
    /**
     * Construct a multiple inheritance exception.
     *
     * @param array<string> $classNames The class names.
     */
    public function __construct(array $classNames)
    {
        $this->classNames = $classNames;

        parent::__construct(
            sprintf(
                'Unable to extend %s simultaneously.',
                implode(
                    ' and ',
                    array_map(
                        function ($className) {
                            return var_export($className, true);
                        },
                        $classNames
                    )
                )
            )
        );
    }

    /**
     * Get the class names.
     *
     * @return array<string> The class names.
     */
    public function classNames(): array
    {
        return $this->classNames;
    }

    private $classNames;
}
