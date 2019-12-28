<?php

declare(strict_types=1);

namespace Eloquent\Phony\Assertion\Exception;

use Exception;
use ReflectionClass;

/**
 * Represents a failed assertion.
 */
final class AssertionException extends Exception
{
    /**
     * Trim the supplied exception's stack trace to only include relevant
     * information.
     *
     * Also replaces the file path and line number.
     *
     * @param Exception $exception The exception.
     */
    public static function trim(Exception $exception): void
    {
        $reflector = new ReflectionClass(Exception::class);

        $traceProperty = $reflector->getProperty('trace');
        $traceProperty->setAccessible(true);
        $fileProperty = $reflector->getProperty('file');
        $fileProperty->setAccessible(true);
        $lineProperty = $reflector->getProperty('line');
        $lineProperty->setAccessible(true);

        $call = static::tracePhonyCall($traceProperty->getValue($exception));

        if (empty($call)) {
            $traceProperty->setValue($exception, []);
            $fileProperty->setValue($exception, null);
            $lineProperty->setValue($exception, null);
        } else {
            $traceProperty->setValue($exception, [$call]);
            $fileProperty->setValue($exception, $call['file'] ?? null);
            $lineProperty->setValue($exception, $call['line'] ?? null);
        }
    }

    /**
     * Find the Phony entry point call in a stack trace.
     *
     * @param array<int,array<string,mixed>> $trace The stack trace.
     *
     * @return array<string,mixed> The call, or an empty array if unable to determine the entry point.
     */
    public static function tracePhonyCall(array $trace): array
    {
        $prefix = 'Eloquent\Phony\\';

        for ($i = count($trace) - 1; $i >= 0; --$i) {
            $entry = $trace[$i];

            if (isset($entry['class'])) {
                if (0 === strpos($entry['class'], $prefix)) {
                    return $entry;
                }
            } elseif (0 === strpos($entry['function'], $prefix)) {
                return $entry;
            }
        }

        return [];
    }

    /**
     * Construct a new assertion exception.
     *
     * @param string $description The failure description.
     */
    public function __construct(string $description)
    {
        parent::__construct($description);

        static::trim($this);
    }
}
