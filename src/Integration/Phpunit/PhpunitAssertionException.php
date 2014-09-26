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
use Exception;
use PHPUnit_Framework_ExpectationFailedException;

/**
 * A PHPUnit assertion failure that wraps a Phony assertion failure.
 */
final class PhpunitAssertionException extends
    PHPUnit_Framework_ExpectationFailedException
{
    /**
     * Construct a new PHPUnit assertion exception.
     *
     * @param AssertionExceptionInterface $failure The failure.
     * @param Exception|null              $cause   The cause, if available.
     */
    public function __construct(
        AssertionExceptionInterface $failure,
        Exception $cause = null
    ) {
        $this->failure = $failure;

        parent::__construct($failure->getMessage(), null, $cause);
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

    private $failure;
}
