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

use Eloquent\Phony\Matcher\AbstractWrappedMatcher;

/**
 * A matcher that wraps a PHPUnit constraint.
 */
class PhpunitMatcher extends AbstractWrappedMatcher
{
    /**
     * Returns true if the supplied value matches.
     *
     * @param mixed $value The value to check.
     *
     * @return boolean True if the value matches.
     */
    public function matches($value)
    {
        return $this->matcher->evaluate($value, null, true) && true;
    }

    /**
     * Describe this matcher.
     *
     * @return string The description.
     */
    public function describe()
    {
        return '<' . $this->matcher->toString() . '>';
    }
}
