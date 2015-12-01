<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2015 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Mock\Proxy\Exception;

use Eloquent\Phony\Mock\Exception\MockExceptionInterface;
use Exception;

/**
 * Call to undefined method.
 */
final class UndefinedMethodException extends Exception implements
    MockExceptionInterface
{
    /**
     * Construct a new undefined method exception.
     *
     * @param string         $className The class name.
     * @param string         $name      The method name.
     * @param Exception|null $cause     The cause, if available.
     */
    public function __construct(
        $className,
        $name,
        Exception $cause = null
    ) {
        $this->className = $className;
        $this->name = $name;

        parent::__construct(
            sprintf(
                'Call to undefined method %s::%s().',
                $className,
                $name
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

    /**
     * Get the method name.
     *
     * @return string The method name.
     */
    public function name()
    {
        return $this->name;
    }

    private $className;
    private $name;
}
