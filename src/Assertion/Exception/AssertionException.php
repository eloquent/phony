<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2015 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Assertion\Exception;

use Exception;
use ReflectionClass;

/**
 * Represents a failed assertion.
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
     * @param Exception $exception The exception.
     */
    public static function trim(Exception $exception)
    {
        $reflector = new ReflectionClass('Exception');

        $traceProperty = $reflector->getProperty('trace');
        $traceProperty->setAccessible(true);
        $fileProperty = $reflector->getProperty('file');
        $fileProperty->setAccessible(true);
        $lineProperty = $reflector->getProperty('line');
        $lineProperty->setAccessible(true);

        $call = static::tracePhonyCall($traceProperty->getValue($exception));

        if ($call) {
            $traceProperty->setValue($exception, array($call));
            $fileProperty->setValue(
                $exception,
                isset($call['file']) ? $call['file'] : null
            );
            $lineProperty->setValue(
                $exception,
                isset($call['line']) ? $call['line'] : null
            );
        } else { // @codeCoverageIgnoreStart
            $traceProperty->setValue($exception, array());
            $fileProperty->setValue($exception, null);
            $lineProperty->setValue($exception, null);
        } // @codeCoverageIgnoreEnd
    }

    /**
     * Find the Phony entry point call in a stack trace.
     *
     * @param array $trace The stack trace.
     *
     * @return array|null The call, or null if unable to determine the entry point.
     */
    public static function tracePhonyCall(array $trace)
    {
        $prefix = 'Eloquent\Phony\\';

        $index = null;
        $broke = false;

        foreach ($trace as $index => $call) {
            if (isset($call['class'])) {
                if (0 !== strpos($call['class'], $prefix)) {
                    $broke = true;

                    break;
                }
            } elseif (0 !== strpos($call['function'], $prefix)) {
                $broke = true;

                break;
            }
        }

        if (null === $index) {
            return;
        }

        if (!$broke) {
            ++$index;
        }

        return $trace[$index - 1];
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
