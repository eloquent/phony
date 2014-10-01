<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Stub;

use Eloquent\Phony\Matcher\Factory\MatcherFactory;
use Eloquent\Phony\Matcher\Factory\MatcherFactoryInterface;
use Eloquent\Phony\Matcher\MatcherInterface;
use Eloquent\Phony\Matcher\Verification\MatcherVerifier;
use Eloquent\Phony\Matcher\Verification\MatcherVerifierInterface;
use Exception;

/**
 * Provides canned answers to function or method invocations.
 *
 * @internal
 */
class Stub implements StubInterface
{
    /**
     * Construct a new stub.
     *
     * @param MatcherFactoryInterface|null  $matcherFactory  The matcher factory to use.
     * @param MatcherVerifierInterface|null $matcherVerifier The matcher verifier to use.
     */
    public function __construct(
        MatcherFactoryInterface $matcherFactory = null,
        MatcherVerifierInterface $matcherVerifier = null
    ) {
        if (null === $matcherFactory) {
            $matcherFactory = MatcherFactory::instance();
        }
        if (null === $matcherVerifier) {
            $matcherVerifier = MatcherVerifier::instance();
        }

        $this->matcherFactory = $matcherFactory;
        $this->matcherVerifier = $matcherVerifier;
        $this->matchers = array($this->matcherFactory->wildcard());
        $this->rules = array();
        $this->ruleCounts = array();
    }

    /**
     * Get the matcher factory.
     *
     * @return MatcherFactoryInterface The matcher factory.
     */
    public function matcherFactory()
    {
        return $this->matcherFactory;
    }

    /**
     * Get the matcher verifier.
     *
     * @return MatcherVerifierInterface The matcher verifier.
     */
    public function matcherVerifier()
    {
        return $this->matcherVerifier;
    }

    /**
     * Modify the current criteria to match the supplied arguments (and possibly
     * others).
     *
     * @param mixed $argument,... The arguments.
     *
     * @return StubInterface This stub.
     */
    public function with()
    {
        $this->matchers = $this->matcherFactory->adaptAll(func_get_args());
        $this->matchers[] = $this->matcherFactory->wildcard();

        return $this;
    }

    /**
     * Modify the current criteria to match the supplied arguments (and no
     * others).
     *
     * @param mixed $argument,... The arguments.
     *
     * @return StubInterface This stub.
     */
    public function withExactly()
    {
        $this->matchers = $this->matcherFactory->adaptAll(func_get_args());

        return $this;
    }

    /**
     * Add a callback as an answer.
     *
     * @param callable $callback                The callback.
     * @param callable $additionalCallbacks,... Additional callbacks for subsequent invocations.
     *
     * @return StubInterface This stub.
     */
    public function does($callback)
    {
        if (isset($this->rules[0]) && $this->rules[0][0] === $this->matchers) {
            foreach (func_get_args() as $callback) {
                array_push($this->rules[0][1], $callback);
            }
        } else {
            array_unshift(
                $this->rules,
                array($this->matchers, func_get_args())
            );
            array_unshift($this->ruleCounts, 0);
        }

        return $this;
    }

    /**
     * Add an answer that returns a value.
     *
     * @param mixed $value                The return value.
     * @param mixed $additionalValues,... Additional return values for subsequent invocations.
     *
     * @return StubInterface This stub.
     */
    public function returns($value = null)
    {
        if (0 === func_num_args()) {
            return $this->does(function () {});
        }

        foreach (func_get_args() as $value) {
            $this->does(
                function () use ($value) {
                    return $value;
                }
            );
        }

        return $this;
    }

    /**
     * Invoke the stub.
     *
     * @param mixed $arguments,...
     *
     * @return mixed     The result of invocation.
     * @throws Exception If the stub throws an exception.
     */
    public function __invoke()
    {
        $arguments = func_get_args();

        foreach ($this->rules as $ruleIndex => &$rule) {
            if ($this->matcherVerifier->matches($rule[0], $arguments)) {
                $this->ruleCounts[$ruleIndex]++;

                if ($callback = current($rule[1])) {
                    next($rule[1]);
                } else {
                    $callback = end($rule[1]);
                }

                return call_user_func_array($callback, $arguments);
            }
        }
    }

    private $matcherFactory;
    private $matcherVerifier;
    private $rules;
    private $ruleCounts;
}
