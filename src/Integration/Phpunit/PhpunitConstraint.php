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

/**
 * A PHPUnit constraint for Phony assertions.
 */
class PhpunitConstraint extends PHPUnit_Framework_Constraint
{
    /**
     * Construct a new PHPUnit constraint.
     *
     * @param string        $description        The description.
     * @param callable      $matchesCallback    The callback to use when determining if the subject matches.
     * @param callable|null $differenceCallback The callback to use when determining the difference.
     */
    public function __construct(
        $description,
        $matchesCallback,
        $differenceCallback = null
    ) {
        parent::__construct();

        $this->description = $description;
        $this->matchesCallback = $matchesCallback;
        $this->differenceCallback = $differenceCallback;
    }

    /**
     * Get the description.
     *
     * @return string The description.
     */
    public function description()
    {
        return $this->description;
    }

    /**
     * Get the matches callback.
     *
     * @return callable The matches callback.
     */
    public function matchesCallback()
    {
        return $this->matchesCallback;
    }

    /**
     * Get the difference callback.
     *
     * @return callable|null The difference callback.
     */
    public function differenceCallback()
    {
        return $this->differenceCallback;
    }

    /**
     * Get the description.
     *
     * @return string The description.
     */
    public function toString()
    {
        return $this->description;
    }

    /**
     * Get the description.
     *
     * @return string The description.
     */
    public function __toString()
    {
        return $this->description;
    }

    /**
     * Evaluate this constraint for the supplied subject.
     *
     * @param mixed   $subject      The subject.
     * @param string  $message      The message.
     * @param boolean $returnResult True if the result should be returned instead of throwing an exception.
     *
     * @return boolean|null                                 A boolean indicating if the constraint is met, or null if not returning results.
     * @throws PHPUnit_Framework_ExpectationFailedException If the constraint is not met.
     */
    public function evaluate($subject, $message = null, $returnResult = null)
    {
        if (null === $message) {
            $message = '';
        }
        if (null === $returnResult) {
            $returnResult = false;
        }

        $isSuccessful = $this->matches($subject);

        if ($returnResult) {
            return $isSuccessful;
        }

        if (!$isSuccessful) {
            if ($this->differenceCallback) {
                $differenceCallback = $this->differenceCallback;
                $difference = $differenceCallback($subject);
            } else {
                $difference = null;
            }

            $this->fail($subject, $message, $difference);
        } // @codeCoverageIgnore
    }

    /**
     * Returns true if the supplied subject matches.
     *
     * @param mixed $subject The subject.
     *
     * @return boolean True if the subject matches.
     */
    protected function matches($subject)
    {
        $matchesCallback = $this->matchesCallback;

        return $matchesCallback($subject);
    }

    private $description;
    private $matchesCallback;
    private $differenceCallback;
}
