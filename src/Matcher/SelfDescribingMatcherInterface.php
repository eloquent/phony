<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2015 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Matcher;

/**
 * The interface implemented by self-describing matchers.
 */
interface SelfDescribingMatcherInterface
{
    /**
     * Describe this matcher.
     *
     * @return string The description.
     */
    public function describe();

    /**
     * Describe this matcher.
     *
     * @return string The description.
     */
    public function __toString();
}
