<?php

declare(strict_types=1);

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2017 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Integration;

use Eloquent\Phony\Matcher\Matchable;
use Eloquent\Phony\Matcher\MatcherDriver;
use Eloquent\Phony\Matcher\WrappedMatcher;
use Hamcrest\Matcher;

/**
 * A matcher driver for Hamcrest matchers.
 */
class HamcrestMatcherDriver implements MatcherDriver
{
    /**
     * Get the static instance of this driver.
     *
     * @return MatcherDriver The static driver.
     */
    public static function instance(): self
    {
        if (!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Returns true if this matcher driver's classes or interfaces exist.
     *
     * @return bool True if available.
     */
    public function isAvailable(): bool
    {
        return interface_exists(Matcher::class);
    }

    /**
     * Get the supported matcher class names.
     *
     * @return array<string> The matcher class names.
     */
    public function matcherClassNames(): array
    {
        return [Matcher::class];
    }

    /**
     * Wrap the supplied third party matcher.
     *
     * @param object $matcher The matcher to wrap.
     *
     * @return Matchable The wrapped matcher.
     */
    public function wrapMatcher($matcher): Matchable
    {
        return new WrappedMatcher($matcher);
    }

    private static $instance;
}
