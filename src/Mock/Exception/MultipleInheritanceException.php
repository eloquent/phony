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
     * @param array<int,string> $classNames The class names.
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
     * @return array<int,string> The class names.
     */
    public function classNames(): array
    {
        return $this->classNames;
    }

    /**
     * @var array<int,string>
     */
    private $classNames;
}
