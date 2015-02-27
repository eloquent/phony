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
 * Undefined property.
 *
 * @internal
 */
final class UndefinedPropertyException extends Exception implements
    MockExceptionInterface
{
    /**
     * Construct a new undefined property exception.
     *
     * @param string         $className The class name.
     * @param string         $name      The property name.
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
                'Undefined property %s::%s().',
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
     * Get the property name.
     *
     * @return string The property name.
     */
    public function name()
    {
        return $this->name;
    }

    private $className;
    private $name;
}
