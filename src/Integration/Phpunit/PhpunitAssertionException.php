<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Integration\Phpunit;

use Exception;
use PHPUnit_Framework_ExpectationFailedException;

/**
 * Wraps PHPUnit's expectation failed exception for improved assertion failure
 * output.
 *
 * @internal
 */
final class PhpunitAssertionException extends
    PHPUnit_Framework_ExpectationFailedException
{
    /**
     * Construct a new PHPUnit assertion exception.
     *
     * @param string         $description The failure description.
     * @param Exception|null $cause       The cause, if available.
     */
    public function __construct($description, Exception $cause = null)
    {
        parent::__construct($description, null, $cause);
    }

    /**
     * Generate a string representation of this assertion failure.
     *
     * @return string The string representation.
     */
    public function __toString()
    {
        foreach ($this->getTrace() as $call) {
            if (0 !== strpos($call['class'], 'Eloquent\Phony\\')) {
                break;
            }

            $file = $call['file'];
            $line = $call['line'];
        }

        return $this->message . "\n\n" . $file . ':' . $line . "\n";
    }
}
