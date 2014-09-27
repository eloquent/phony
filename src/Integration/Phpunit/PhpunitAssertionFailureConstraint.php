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

use PHPUnit_Framework_Constraint;
use PHPUnit_Framework_ExpectationFailedException;

/**
 * A PHPUnit constraint that wraps a Phony assertion failure.
 */
class PhpunitAssertionFailureConstraint extends PHPUnit_Framework_Constraint
{
    /**
     * Construct a new PHPUnit assertion failure constraint.
     *
     * @param string $description The failure description.
     */
    public function __construct($description)
    {
        $this->description = $description;
    }

    /**
     * Get the failure description.
     *
     * @return string The failure description.
     */
    public function toString()
    {
        return $this->description;
    }

    /**
     * Throws the assertion failure exception.
     *
     * @throws PHPUnit_Framework_ExpectationFailedException When called.
     */
    public function evaluate($other, $description = null, $returnResult = null)
    {
        throw new PHPUnit_Framework_ExpectationFailedException(
            $this->description
        );
    }

    private $description;
}
