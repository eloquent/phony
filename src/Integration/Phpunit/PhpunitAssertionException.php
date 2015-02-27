<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2015 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Integration\Phpunit;

use Eloquent\Phony\Assertion\Exception\AssertionException;
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
        AssertionException::trim($this);

        parent::__construct($description, null, $cause);
    }
}
