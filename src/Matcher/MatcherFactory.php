<?php

declare(strict_types=1);

namespace Eloquent\Phony\Matcher;

use Eloquent\Phony\Exporter\Exporter;
use InvalidArgumentException;

/**
 * Creates matchers.
 */
class MatcherFactory
{
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
     * Create new matchers for all supplied values.
     *
     * @param array<int|string,mixed> $values The values to create matchers for.
     *
     * @return array<int|string,Matcher> The newly created matchers.
     */
    public function adaptAll(array $values): array
    {
        $matchers = [];

        foreach ($values as $key => $value) {
            if ($value instanceof Matcher) {
                $matchers[$key] = $value;

                continue;
            }

            if (is_object($value)) {
                foreach ($this->driverIndex as $className => $driver) {
                    if (is_a($value, $className)) {
                        $matchers[$key] = $driver->wrapMatcher($value);

                        continue 2;
                    }
                }
            }

            if ('*' === $value) {
                $matchers[$key] = $this->wildcardAnyMatcher;
            } elseif ('~' === $value) {
                $matchers[$key] = $this->anyMatcher;
            } else {
                $matchers[$key] =
                    new EqualToMatcher($value, true, $this->exporter);
            }
        }

        return $matchers;
    }

    /**
     * Create a new matcher set for the supplied values.
     *
     * @param array<int,string>       $parameterNames The parameter names.
     * @param array<int|string,mixed> $values         The values to create matchers for.
     *
     * @return MatcherSet               The newly created matcher set.
     * @throws InvalidArgumentException If the supplied values are invalid.
     */
    public function adaptSet(array $parameterNames, array $values): MatcherSet
    {
        $keyMap = [];
        $declaredMatchers = [];

        foreach ($parameterNames as $position => $name) {
            $keyMap[$position] = $name;
            $keyMap[$name] = $position;

            $declaredMatchers[$position] = null;
        }

        $declaredCount = count($parameterNames);
        $variadicMatchers = [];
        $wildcardMatcher = null;
        $hasNamedMatcher = false;
        $position = -1;

        foreach ($values as $key => $value) {
            $matcher = null;

            if ($value instanceof Matcher) {
                $matcher = $value;
            } elseif (is_object($value)) {
                foreach ($this->driverIndex as $className => $driver) {
                    if (is_a($value, $className)) {
                        $matcher = $driver->wrapMatcher($value);

                        break;
                    }
                }
            } elseif ('*' === $value) {
                $matcher = $this->wildcardAnyMatcher;
            } elseif ('~' === $value) {
                $matcher = $this->anyMatcher;
            }

            if (!$matcher) {
                $matcher = new EqualToMatcher($value, true, $this->exporter);
            }

            $isPositional = is_int($key);

            if ($matcher instanceof WildcardMatcher) {
                if (!$isPositional) {
                    throw new InvalidArgumentException(
                        'Cannot use a named wildcard matcher.'
                    );
                }
                if ($hasNamedMatcher) {
                    throw new InvalidArgumentException(
                        'Cannot use a wildcard matcher after a named matcher.'
                    );
                }
                if ($wildcardMatcher) {
                    throw new InvalidArgumentException(
                        'Cannot use a wildcard matcher ' .
                        'after a wildcard matcher.'
                    );
                }

                $wildcardMatcher = $matcher;

                continue;
            }

            ++$position;

            if ($isPositional) {
                if ($hasNamedMatcher) {
                    throw new InvalidArgumentException(
                        'Cannot use a positional matcher after a named matcher.'
                    );
                }
                if ($wildcardMatcher) {
                    throw new InvalidArgumentException(
                        'Cannot use a positional matcher ' .
                        'after a wildcard matcher.'
                    );
                }

                if ($position < $declaredCount) {
                    $declaredMatchers[$position] = $matcher;
                } else {
                    $variadicMatchers[$position] = $matcher;
                }
            } else {
                $hasNamedMatcher = true;

                if (isset($keyMap[$key])) {
                    /** @var int $mappedKey */
                    $mappedKey = $keyMap[$key];

                    if (isset($declaredMatchers[$mappedKey])) {
                        throw new InvalidArgumentException(
                            "Named matcher $$key overwrites previous matcher."
                        );
                    }

                    $declaredMatchers[$mappedKey] = $matcher;
                } else {
                    $variadicMatchers[$key] = $matcher;
                }
            }
        }

        uksort($variadicMatchers, [__CLASS__, 'compareVariadicKeys']);

        return new MatcherSet(
            parameterNames: $parameterNames,
            keyMap: $keyMap,
            declaredCount: $declaredCount,
            declaredMatchers: $declaredMatchers,
            variadicMatchers: $variadicMatchers,
            wildcardMatcher: $wildcardMatcher,
            wildcardInnerMatcher: $wildcardMatcher?->matcher(),
            wildcardMinimum: $wildcardMatcher?->minimumArguments() ?? 0,
            wildcardMaximum: $wildcardMatcher?->maximumArguments() ?? -1,
        );
    }

    /**
     * Create a new matcher set for the supplied parameter names that matches
     * any number and type of arguments.
     *
     * @param array<int,string> $parameterNames The parameter names.
     *
     * @return MatcherSet The newly created matcher set.
     */
    public function wildcardAnySet(array $parameterNames): MatcherSet
    {
        $keyMap = [];

        foreach ($parameterNames as $position => $name) {
            $keyMap[$position] = $name;
            $keyMap[$name] = $position;
        }

        return new MatcherSet(
            parameterNames: $parameterNames,
            keyMap: $keyMap,
            declaredCount: 0,
            declaredMatchers: [],
            variadicMatchers: [],
            wildcardMatcher: $this->wildcardAnyMatcher,
            wildcardInnerMatcher: $this->anyMatcher,
            wildcardMinimum: 0,
            wildcardMaximum: -1,
        );
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

    private static function compareVariadicKeys(
        int|string $a,
        int|string $b,
    ): int {
        $aIsPositional = is_int($a);
        $bIsPositional = is_int($b);

        if ($aIsPositional && !$bIsPositional) {
            return -1;
        }
        if (!$aIsPositional && $bIsPositional) {
            return 1;
        }

        return $a < $b ? -1 : 1;
    }
}
