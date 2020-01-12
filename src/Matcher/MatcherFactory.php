<?php

declare(strict_types=1);

namespace Eloquent\Phony\Matcher;

use Eloquent\Phony\Exporter\Exporter;
use Eloquent\Phony\Exporter\InlineExporter;
use Eloquent\Phony\Hamcrest\HamcrestMatcherDriver;

/**
 * Creates matchers.
 */
class MatcherFactory
{
    /**
     * Get the static instance of this class.
     *
     * @return self The static instance.
     */
    public static function instance(): self
    {
        if (!self::$instance) {
            $instance = new self(
                AnyMatcher::instance(),
                WildcardMatcher::instance(),
                InlineExporter::instance()
            );
            $instance->addMatcherDriver(HamcrestMatcherDriver::instance());

            self::$instance = $instance;
        }

        return self::$instance;
    }

    /**
     * Construct a new matcher factory.
     *
     * @param Matcher         $anyMatcher         A matcher that matches any value.
     * @param WildcardMatcher $wildcardAnyMatcher A matcher that matches any number of arguments of any value.
     * @param Exporter        $exporter           The exporter to use.
     */
    public function __construct(
        Matcher $anyMatcher,
        WildcardMatcher $wildcardAnyMatcher,
        Exporter $exporter
    ) {
        $this->drivers = [];
        $this->driverIndex = [];
        $this->anyMatcher = $anyMatcher;
        $this->wildcardAnyMatcher = $wildcardAnyMatcher;
        $this->exporter = $exporter;
    }

    /**
     * Add a matcher driver.
     *
     * @param MatcherDriver $driver The matcher driver.
     */
    public function addMatcherDriver(MatcherDriver $driver): void
    {
        if (!in_array($driver, $this->drivers, true)) {
            $this->drivers[] = $driver;

            if ($driver->isAvailable()) {
                foreach ($driver->matcherClassNames() as $className) {
                    $this->driverIndex[$className] = $driver;
                }
            }
        }
    }

    /**
     * Get the matcher drivers.
     *
     * @return array<int,MatcherDriver> The matcher drivers.
     */
    public function drivers(): array
    {
        return $this->drivers;
    }

    /**
     * Returns true if the supplied value is a matcher.
     *
     * @param mixed $value The value to test.
     *
     * @return bool True if the value is a matcher.
     */
    public function isMatcher($value): bool
    {
        if (is_object($value)) {
            if ($value instanceof Matcher) {
                return true;
            }

            foreach ($this->driverIndex as $className => $driver) {
                if (is_a($value, $className)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Create a new matcher for the supplied value.
     *
     * @param mixed $value The value to create a matcher for.
     *
     * @return Matcher The newly created matcher.
     */
    public function adapt($value): Matcher
    {
        if ($value instanceof Matcher) {
            return $value;
        }

        if (is_object($value)) {
            foreach ($this->driverIndex as $className => $driver) {
                if (is_a($value, $className)) {
                    return $driver->wrapMatcher($value);
                }
            }
        }

        if ('~' === $value) {
            return $this->anyMatcher;
        }

        return new EqualToMatcher($value, true, $this->exporter);
    }

    /**
     * Create new matchers for the all supplied values.
     *
     * @param array<int,mixed> $values The values to create matchers for.
     *
     * @return array<int,Matcher> The newly created matchers.
     */
    public function adaptAll(array $values): array
    {
        $matchers = [];

        foreach ($values as $value) {
            if ($value instanceof Matcher) {
                $matchers[] = $value;

                continue;
            }

            if (is_object($value)) {
                foreach ($this->driverIndex as $className => $driver) {
                    if (is_a($value, $className)) {
                        $matchers[] = $driver->wrapMatcher($value);

                        continue 2;
                    }
                }
            }

            if ('*' === $value) {
                $matchers[] = $this->wildcardAnyMatcher;
            } elseif ('~' === $value) {
                $matchers[] = $this->anyMatcher;
            } else {
                $matchers[] = new EqualToMatcher($value, true, $this->exporter);
            }
        }

        return $matchers;
    }

    /**
     * Create a new matcher that matches anything.
     *
     * @return Matcher The newly created matcher.
     */
    public function any(): Matcher
    {
        return $this->anyMatcher;
    }

    /**
     * Create a new equal to matcher.
     *
     * @param mixed $value           The value to check against.
     * @param bool  $useSubstitution True to use substitution for wrapper types.
     *
     * @return Matcher The newly created matcher.
     */
    public function equalTo($value, bool $useSubstitution = false): Matcher
    {
        return new EqualToMatcher($value, $useSubstitution, $this->exporter);
    }

    /**
     * Create a new instance of matcher.
     *
     * @param string|object $type The type to check against.
     *
     * @return Matcher The newly created matcher.
     */
    public function anInstanceOf($type): Matcher
    {
        if (is_object($type)) {
            $type = get_class($type);
        }

        return new InstanceOfMatcher($type);
    }

    /**
     * Create a new matcher that matches multiple arguments.
     *
     * Negative values for $maximumArguments are treated as "no maximum".
     *
     * @param mixed $value            The value to check for each argument.
     * @param int   $minimumArguments The minimum number of arguments.
     * @param int   $maximumArguments The maximum number of arguments.
     *
     * @return WildcardMatcher The newly created wildcard matcher.
     */
    public function wildcard(
        $value = null,
        int $minimumArguments = 0,
        int $maximumArguments = -1
    ): WildcardMatcher {
        if (0 === func_num_args()) {
            return $this->wildcardAnyMatcher;
        }

        if (null === $value) {
            $value = $this->anyMatcher;
        } else {
            $value = $this->adapt($value);
        }

        return
            new WildcardMatcher($value, $minimumArguments, $maximumArguments);
    }

    /**
     * @var ?self
     */
    private static $instance;

    /**
     * @var array<int,MatcherDriver>
     */
    private $drivers;

    /**
     * @var array<string,MatcherDriver>
     */
    private $driverIndex;

    /**
     * @var Matcher
     */
    private $anyMatcher;

    /**
     * @var WildcardMatcher
     */
    private $wildcardAnyMatcher;

    /**
     * @var Exporter
     */
    private $exporter;
}
