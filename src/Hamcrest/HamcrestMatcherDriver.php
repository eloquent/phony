<?php

declare(strict_types=1);

namespace Eloquent\Phony\Hamcrest;

use Eloquent\Phony\Matcher\Matcher;
use Eloquent\Phony\Matcher\MatcherDriver;
use Hamcrest\Matcher as HamcrestMatcherInterface;

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
        return interface_exists(HamcrestMatcherInterface::class);
    }

    /**
     * Get the supported matcher class names.
     *
     * @return array<string> The matcher class names.
     */
    public function matcherClassNames(): array
    {
        return [HamcrestMatcherInterface::class];
    }

    /**
     * Wrap the supplied third party matcher.
     *
     * @param object $matcher The matcher to wrap.
     *
     * @return Matcher The wrapped matcher.
     */
    public function wrapMatcher($matcher): Matcher
    {
        return new HamcrestMatcher($matcher);
    }

    /**
     * @var ?self
     */
    private static $instance;
}
