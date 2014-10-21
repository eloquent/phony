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

/**
 * Represents a failed assertion.
 *
 * @internal
 */
final class AssertionException extends Exception implements
    AssertionExceptionInterface
{
    /**
     * Construct a new assertion exception.
     *
     * @param string         $description The failure description.
     * @param Exception|null $cause       The cause, if available.
     */
    public function __construct($description, Exception $cause = null)
    {
        parent::__construct($description, 0, $cause);
    }
}
