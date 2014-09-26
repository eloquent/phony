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

use Eloquent\Phony\Assertion\Exception\AssertionExceptionInterface;
use PHPUnit_Framework_Constraint;

/**
 * A PHPUnit constraint that wraps a Phony assertion failure.
 */
class PhpunitAssertionFailureConstraint extends PHPUnit_Framework_Constraint
{
    /**
     * Construct a new PHPUnit assertion failure constraint.
     *
     * @param AssertionExceptionInterface $failure The failure.
     */
    public function __construct(AssertionExceptionInterface $failure)
    {
        $this->failure = $failure;
    }

    /**
     * Get the failure.
     *
     * @return AssertionExceptionInterface The failure.
     */
    public function failure()
    {
        return $this->failure;
    }

    /**
     * Throws the wrapped assertion failure exception.
     *
     * @throws PhpunitAssertionException When called.
     */
    public function evaluate($other, $description = null, $returnResult = null)
    {
        throw new PhpunitAssertionException($this->failure);
    }

    /**
     * Get a string representation of this constraint.
     *
     * @return string A string representation of this constraint.
     */
    public function toString()
    {
        return $this->failure->getMessage();
    }

    private $failure;
}
