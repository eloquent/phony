<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Matcher;

/**
 * The interface implemented by wrapped matchers.
 */
interface WrappedMatcherInterface extends MatcherInterface
{
    /**
     * Get the wrapped matcher.
     *
     * @return object The matcher.
     */
    public function matcher();
}
