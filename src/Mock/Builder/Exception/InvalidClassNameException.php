<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Mock\Builder\Exception;

use Exception;

/**
 * The supplied class name is invalid.
 *
 * @internal
 */
final class InvalidClassNameException extends Exception implements
    MockBuilderExceptionInterface
{
    /**
     * Construct a new invalid class name exception.
     *
     * @param mixed          $className The class name.
     * @param Exception|null $cause     The cause, if available.
     */
    public function __construct($className, Exception $cause = null)
    {
        $this->className = $className;

        parent::__construct(
            sprintf('Invalid class name %s.', var_export($className, true)),
            0,
            $cause
        );
    }

    /**
     * Get the class name.
     *
     * @return mixed The class name.
     */
    public function className()
    {
        return $this->className;
    }

    private $className;
}
