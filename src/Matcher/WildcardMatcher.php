<?php

declare(strict_types=1);

namespace Eloquent\Phony\Matcher;

use Eloquent\Phony\Exporter\Exporter;

/**
 * A matcher that tests any number of arguments against another matcher.
 */
class WildcardMatcher implements Matcher
{
    /**
     * Get the static instance of this class.
     *
     * @return self The static instance.
     */
    public static function instance(): self
    {
        if (!self::$instance) {
            self::$instance = new self(AnyMatcher::instance(), 0, -1);
        }

        return self::$instance;
    }

    /**
     * Construct a new wildcard matcher.
     *
     * Negative values for $maximumArguments are treated as "no maximum".
     *
     * @param Matcher $matcher          The matcher to use for each argument.
     * @param int     $minimumArguments The minimum number of arguments.
     * @param int     $maximumArguments The maximum number of arguments.
     */
    public function __construct(
        Matcher $matcher,
        int $minimumArguments,
        int $maximumArguments
    ) {
        $this->matcher = $matcher;
        $this->minimumArguments = $minimumArguments;
        $this->maximumArguments = $maximumArguments;
    }

    /**
     * Get the matcher to use for each argument.
     *
     * @return Matcher The matcher.
     */
    public function matcher(): Matcher
    {
        return $this->matcher;
    }

    /**
     * Get the minimum number of arguments to match.
     *
     * @return int The minimum number of arguments.
     */
    public function minimumArguments(): int
    {
        return $this->minimumArguments;
    }

    /**
     * Get the maximum number of arguments to match.
     *
     * @return int The maximum number of arguments.
     */
    public function maximumArguments(): int
    {
        return $this->maximumArguments;
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
        $matcherDescription = $this->matcher->describe($exporter);

        if (0 === $this->minimumArguments) {
            if ($this->maximumArguments < 0) {
                return sprintf('%s*', $matcherDescription);
            } else {
                return sprintf(
                    '%s{,%d}',
                    $matcherDescription,
                    $this->maximumArguments
                );
            }
        } elseif ($this->maximumArguments < 0) {
            return sprintf(
                '%s{%d,}',
                $matcherDescription,
                $this->minimumArguments
            );
        } elseif ($this->minimumArguments === $this->maximumArguments) {
            return sprintf(
                '%s{%d}',
                $matcherDescription,
                $this->minimumArguments
            );
        }

        return sprintf(
            '%s{%d,%d}',
            $matcherDescription,
            $this->minimumArguments,
            $this->maximumArguments
        );
    }

    /**
     * Describe this matcher.
     *
     * @return string The description.
     */
    public function __toString(): string
    {
        return $this->describe();
    }

    /**
     * Always returns false.
     *
     * @param mixed $value The value to check.
     *
     * @return false For all values.
     */
    public function matches($value): bool
    {
        return false;
    }

    /**
     * @var ?self
     */
    private static $instance;

    /**
     * @var Matcher
     */
    private $matcher;

    /**
     * @var int
     */
    private $minimumArguments;

    /**
     * @var int
     */
    private $maximumArguments;
}
