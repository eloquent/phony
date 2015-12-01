<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2015 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Mock\Exception;

use Exception;

/**
 * Unable to extend multiple classes.
 */
final class MultipleInheritanceException extends Exception implements
    MockExceptionInterface
{
    /**
     * Construct a multiple inheritance exception.
     *
     * @param array<string>  $classNames The class names.
     * @param Exception|null $cause      The cause, if available.
     */
    public function __construct(array $classNames, Exception $cause = null)
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
            ),
            0,
            $cause
        );
    }

    /**
     * Get the class names.
     *
     * @return array<string> The class names.
     */
    public function classNames()
    {
        return $this->classNames;
    }

    private $classNames;
}
