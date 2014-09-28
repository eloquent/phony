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
 * The interface implemented by matcher drivers.
 */
interface MatcherDriverInterface
{
    /**
     * Returns true if the supplied matcher is supported by this driver.
     *
     * @param object $matcher The matcher to test.
     *
     * @return boolean True if supported.
     */
    public function isSupported($matcher);

    /**
     * If the supplied matcher is supported, replace it with an equivalent Phony
     * matcher.
     *
     * @param object &$matcher The matcher to adapt.
     *
     * @return boolean True if the matcher is supported.
     */
    public function adapt(&$matcher);
}
