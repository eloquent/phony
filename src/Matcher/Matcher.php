<?php

declare(strict_types=1);

namespace Eloquent\Phony\Matcher;

use Eloquent\Phony\Exporter\Exporter;

/**
 * The interface implemented by matchers.
 */
interface Matcher
{
    /**
     * Returns `true` if `$value` matches this matcher's criteria.
     *
     * @param mixed $value The value to check.
     *
     * @return bool True if the value matches.
     */
    public function matches($value): bool;

    /**
     * Describe this matcher.
     *
     * @param ?Exporter $exporter The exporter to use.
     *
     * @return string The description.
     */
    public function describe(Exporter $exporter = null): string;

    /**
     * Describe this matcher.
     *
     * @return string The description.
     */
    public function __toString(): string;
}
