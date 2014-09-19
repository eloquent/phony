<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Matcher\Factory;

use Eloquent\Phony\Matcher\MatcherInterface;

/**
 * The interface implemented by matcher factories.
 */
interface MatcherFactoryInterface
{
    /**
     * Create a new matcher for the supplied value.
     *
     * @param mixed $value The value to create a matcher for.
     *
     * @return MatcherInterface The newly created matcher.
     */
    public function adapt($value);

    /**
     * Create new matchers for the all supplied values.
     *
     * @param array<integer,mixed> $values The values to create matchers for.
     *
     * @return array<integer,MatcherInterface> The newly created matchers.
     */
    public function adaptAll(array $values);

    /**
     * Create a new equal to matcher.
     *
     * @param mixed $value The value to check.
     *
     * @return MatcherInterface The newly created matcher.
     */
    public function equalTo($value);
}
