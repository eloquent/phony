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

use Eloquent\Phony\Matcher\EqualToMatcher;
use Eloquent\Phony\Matcher\MatcherInterface;
use Eloquent\Phony\Matcher\WrappedMatcher;

/**
 * Creates matchers.
 */
class MatcherFactory implements MatcherFactoryInterface
{
    /**
     * Get the static instance of this factory.
     *
     * @return MatcherFactoryInterface The static factory.
     */
    public static function instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Create a new matcher for the supplied value.
     *
     * @param mixed $value The value to create a matcher for.
     *
     * @return MatcherInterface The newly created matcher.
     */
    public function adapt($value)
    {
        if ($value instanceof MatcherInterface) {
            return $value;
        }
        if (is_object($value) && is_a($value, 'Hamcrest\Matcher')) {
            return new WrappedMatcher($value);
        }

        return $this->equalTo($value);
    }

    /**
     * Create new matchers for the all supplied values.
     *
     * @param array<integer,mixed> $values The values to create matchers for.
     *
     * @return array<integer,MatcherInterface> The newly created matchers.
     */
    public function adaptAll(array $values)
    {
        $matchers = array();
        foreach ($values as $value) {
            $matchers[] = $this->adapt($value);
        }

        return $matchers;
    }

    /**
     * Create a new equal to matcher.
     *
     * @param mixed $value The value to check.
     *
     * @return MatcherInterface The newly created matcher.
     */
    public function equalTo($value)
    {
        return new EqualToMatcher($value);
    }

    private static $instance;
}
