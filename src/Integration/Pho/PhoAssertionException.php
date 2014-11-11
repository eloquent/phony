<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Integration\Pho;

use Exception;

/**
 * Emulates Pho's expectation exception for improved assertion failure output.
 *
 * @internal
 */
class PhoAssertionException extends Exception
{
    /**
     * Construct a new Pho assertion exception.
     *
     * @param string         $description The failure description.
     * @param Exception|null $cause       The cause, if available.
     */
    public function __construct($description, Exception $cause = null)
    {
        parent::__construct($description, 0, $cause);

        foreach (\debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS) as $call) {
            if (0 !== strpos($call['class'], 'Eloquent\Phony\\')) {
                break;
            }

            $this->file = $call['file'];
            $this->line = $call['line'];
        }
    }

    /**
     * Generate a string representation of this assertion failure.
     *
     * @return string The string representation.
     */
    public function __toString()
    {
        return "{$this->file}:{$this->line}\n$this->message";
    }
}
