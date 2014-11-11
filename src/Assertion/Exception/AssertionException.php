<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Assertion\Exception;

use Exception;
use ReflectionClass;

/**
 * Represents a failed assertion.
 *
 * @internal
 */
final class AssertionException extends Exception implements
    AssertionExceptionInterface
{
    /**
     * Trim the supplied exception's stack trace to only include relevant
     * information.
     *
     * Also replaces the file path and line number.
     *
     * @param Exception   $exception The exception.
     * @param string|null $prefix    The class name prefix to search for.
     */
    public static function trim(Exception $exception, $prefix = null)
    {
        if (null === $prefix) {
            $prefix = 'Eloquent\Phony\\';
        }

        $reflector = new ReflectionClass('Exception');

        $traceProperty = $reflector->getProperty('trace');
        $traceProperty->setAccessible(true);
        $fileProperty = $reflector->getProperty('file');
        $fileProperty->setAccessible(true);
        $lineProperty = $reflector->getProperty('line');
        $lineProperty->setAccessible(true);

        $trace = $traceProperty->getValue($exception);
        $index = null;
        $broke = false;

        foreach ($trace as $index => $call) {
            if (
                !isset($call['class']) ||
                0 !== strpos($call['class'], $prefix)
            ) {
                $broke = true;

                break;
            }
        }

        if (!$broke) {
            $index++;
        }

        if (null === $index) {
            $traceProperty->setValue($exception, array());
            $fileProperty->setValue($exception, null);
            $lineProperty->setValue($exception, null);
        } else {
            $trace = array_slice($trace, $index - 1, 1);

            $traceProperty->setValue($exception, $trace);
            $fileProperty->setValue(
                $exception,
                isset($trace[0]['file']) ? $trace[0]['file'] : null
            );
            $lineProperty->setValue(
                $exception,
                isset($trace[0]['line']) ? $trace[0]['line'] : null
            );
        }
    }

    /**
     * Construct a new assertion exception.
     *
     * @param string         $description The failure description.
     * @param Exception|null $cause       The cause, if available.
     */
    public function __construct($description, Exception $cause = null)
    {
        parent::__construct($description, 0, $cause);

        static::trim($this);
    }
}
