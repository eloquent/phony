<?php

declare(strict_types=1);

namespace Eloquent\Phony\Matcher;

/**
 * The interface implemented by matcher drivers.
 */
interface MatcherDriver
{
    /**
     * Returns true if this matcher driver's classes or interfaces exist.
     *
     * @return bool True if available.
     */
    public function isAvailable(): bool;

    /**
     * Get the supported matcher class names.
     *
     * @return array<string> The matcher class names.
     */
    public function matcherClassNames(): array;

    /**
     * Wrap the supplied third party matcher.
     *
     * @param object $matcher The matcher to wrap.
     *
     * @return Matchable The wrapped matcher.
     */
    public function wrapMatcher($matcher): Matchable;
}
