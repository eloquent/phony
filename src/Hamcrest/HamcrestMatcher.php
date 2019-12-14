<?php

declare(strict_types=1);

namespace Eloquent\Phony\Hamcrest;

use Eloquent\Phony\Exporter\Exporter;
use Eloquent\Phony\Matcher\Matcher;
use Hamcrest\Matcher as ExternalMatcher;

/**
 * Wraps a Hamcrest matcher.
 */
class HamcrestMatcher implements Matcher
{
    /**
     * Construct a new Hamcrest matcher.
     *
     * @param ExternalMatcher $matcher The matcher to wrap.
     */
    public function __construct(ExternalMatcher $matcher)
    {
        $this->matcher = $matcher;
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
        return (bool) $this->matcher->matches($value);
    }

    /**
     * Describe this matcher.
     *
     * @param ?Exporter $exporter The exporter to use.
     *
     * @return string The description.
     */
    public function describe(Exporter $exporter = null): string
    {
        return '<' . strval($this->matcher) . '>';
    }

    /**
     * Describe this matcher.
     *
     * @return string The description.
     */
    public function __toString(): string
    {
        return '<' . strval($this->matcher) . '>';
    }

    /**
     * @var ExternalMatcher
     */
    private $matcher;
}
