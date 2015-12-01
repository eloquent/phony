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
 * The class is already defined.
 */
final class ClassExistsException extends Exception implements
    MockExceptionInterface
{
    /**
     * Construct a class exists exception.
     *
     * @param string         $className The class name.
     * @param Exception|null $cause     The cause, if available.
     */
    public function __construct($className, Exception $cause = null)
    {
        $this->className = $className;

        parent::__construct(
            sprintf(
                'Class %s is already defined.',
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
    public function className()
    {
        return $this->className;
    }

    private $className;
}
