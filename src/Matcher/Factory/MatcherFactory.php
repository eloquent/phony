<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Matcher\Factory;

use Eloquent\Phony\Matcher\EqualToMatcher;
use Eloquent\Phony\Matcher\Integration\HamcrestMatcher;
use Eloquent\Phony\Matcher\Integration\PhpunitMatcher;
use Eloquent\Phony\Matcher\Integration\ProphecyMatcher;
use Eloquent\Phony\Matcher\Integration\SimpletestMatcher;
use Eloquent\Phony\Matcher\MatcherInterface;

/**
 * Creates matchers.
 */
class MatcherFactory implements MatcherFactoryInterface
{
    /**
     * Get the static instance of this factory.
     *
     * @return MatcherFactoryInterface The static factory.
     */
    public static function instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Get the default integration map.
     *
     * This is used to map matcher classes from other libraries to the
     * appropriate wrapper class.
     *
     * @return array<string,string> The default integration map.
     */
    public static function defaultIntegrationMap()
    {
        return array(
            'Hamcrest\Matcher' =>
                'Eloquent\Phony\Matcher\Integration\HamcrestMatcher',
            'PHPUnit_Framework_Constraint' =>
                'Eloquent\Phony\Matcher\Integration\PhpunitMatcher',
            'Phake_Matchers_IArgumentMatcher' =>
                'Eloquent\Phony\Matcher\Integration\PhakeMatcher',
            'Prophecy\Argument\Token\TokenInterface' =>
                'Eloquent\Phony\Matcher\Integration\ProphecyMatcher',
            'Mockery\Matcher\MatcherAbstract' =>
                'Eloquent\Phony\Matcher\Integration\MockeryMatcher',
            'SimpleExpectation' =>
                'Eloquent\Phony\Matcher\Integration\SimpletestMatcher',
        );
    }

    /**
     * Construct a new matcher factory.
     *
     * @param array<string,string>|null $integrationMap The integration map to use.
     */
    public function __construct(array $integrationMap = null)
    {
        if (null === $integrationMap) {
            $integrationMap = static::defaultIntegrationMap();
        }

        $this->integrationMap = $integrationMap;
    }

    /**
     * Set the integration map.
     *
     * @param array<string,string> $integrationMap The integration map.
     */
    public function setIntegrationMap(array $integrationMap)
    {
        $this->integrationMap = $integrationMap;
    }

    /**
     * Add an entry to the integration map.
     *
     * @param string $className        The class name of the foreign matcher.
     * @param string $wrapperClassName The class name of the wrapper class to use.
     */
    public function addIntegrationMapEntry($className, $wrapperClassName)
    {
        $this->integrationMap[$className] = $wrapperClassName;
    }

    /**
     * Get the integration map.
     *
     * @return array<string,string> The integration map.
     */
    public function integrationMap()
    {
        return $this->integrationMap;
    }

    /**
     * Create a new matcher for the supplied value.
     *
     * @param mixed $value The value to create a matcher for.
     *
     * @return MatcherInterface The newly created matcher.
     */
    public function adapt($value)
    {
        if ($value instanceof MatcherInterface) {
            return $value;
        }

        if (is_object($value)) {
            foreach ($this->integrationMap as $className => $wrapperClassName) {
                if (is_a($value, $className)) {
                    return new $wrapperClassName($value);
                }
            }
        }

        return $this->equalTo($value);
    }

    /**
     * Create new matchers for the all supplied values.
     *
     * @param array<integer,mixed> $values The values to create matchers for.
     *
     * @return array<integer,MatcherInterface> The newly created matchers.
     */
    public function adaptAll(array $values)
    {
        $matchers = array();
        foreach ($values as $value) {
            $matchers[] = $this->adapt($value);
        }

        return $matchers;
    }

    /**
     * Create a new equal to matcher.
     *
     * @param mixed $value The value to check.
     *
     * @return MatcherInterface The newly created matcher.
     */
    public function equalTo($value)
    {
        return new EqualToMatcher($value);
    }

    private static $instance;
    private $integrationMap;
}
