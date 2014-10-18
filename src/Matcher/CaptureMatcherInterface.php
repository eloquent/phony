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
 * The interface implemented by capture matchers.
 */
interface CaptureMatcherInterface extends WrappedMatcherInterface
{
    /**
     * Get the value.
     *
     * @return mixed The value.
     */
    public function value();
}
