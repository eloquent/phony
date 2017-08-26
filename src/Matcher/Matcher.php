<?php

declare(strict_types=1);

namespace Eloquent\Phony\Matcher;

/**
 * The interface implemented by matchers.
 */
interface Matcher extends Matchable
{
    /**
     * Returns `true` if `$value` matches this matcher's criteria.
     *
     * @param mixed $value The value to check.
     *
     * @return bool True if the value matches.
     */
    public function matches($value): bool;
}
