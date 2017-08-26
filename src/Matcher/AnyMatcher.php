<?php

declare(strict_types=1);

namespace Eloquent\Phony\Matcher;

use Eloquent\Phony\Exporter\Exporter;

/**
 * A matcher that always returns true.
 */
class AnyMatcher implements Matcher
{
    /**
     * Get the static instance of this matcher.
     *
     * @return Matcher The static matcher.
     */
    public static function instance(): self
    {
        if (!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Returns `true` if `$value` matches this matcher's criteria.
     *
     * @param mixed $value The value to check.
     *
     * @return bool True if the value matches.
     */
    public function matches($value): bool
    {
        return true;
    }

    /**
     * Describe this matcher.
     *
     * @param Exporter|null $exporter The exporter to use.
     *
     * @return string The description.
     */
    public function describe(Exporter $exporter = null): string
    {
        return '<any>';
    }

    /**
     * Describe this matcher.
     *
     * @return string The description.
     */
    public function __toString(): string
    {
        return '<any>';
    }

    private static $instance;
}
