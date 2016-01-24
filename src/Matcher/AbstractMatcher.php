<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2016 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Matcher;

/**
 * An abstract base class for implementing matchers.
 */
abstract class AbstractMatcher implements MatcherInterface
{
    /**
     * Describe this matcher.
     *
     * @return string The description.
     */
    public function __toString()
    {
        return $this->describe();
    }
}
