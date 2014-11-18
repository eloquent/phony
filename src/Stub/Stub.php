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

use Closure;
use Eloquent\Phony\Call\Argument\Arguments;
use Eloquent\Phony\Call\Argument\ArgumentsInterface;
use Eloquent\Phony\Feature\FeatureDetector;
use Eloquent\Phony\Feature\FeatureDetectorInterface;
use Eloquent\Phony\Invocation\AbstractWrappedInvocable;
use Eloquent\Phony\Invocation\InvocableInspector;
use Eloquent\Phony\Invocation\InvocableInspectorInterface;
use Eloquent\Phony\Invocation\Invoker;
use Eloquent\Phony\Invocation\InvokerInterface;
use Eloquent\Phony\Matcher\Factory\MatcherFactory;
use Eloquent\Phony\Matcher\Factory\MatcherFactoryInterface;
use Eloquent\Phony\Matcher\Verification\MatcherVerifier;
use Eloquent\Phony\Matcher\Verification\MatcherVerifierInterface;
use Eloquent\Phony\Stub\Answer\Answer;
use Eloquent\Phony\Stub\Answer\CallRequest;
use Eloquent\Phony\Stub\Rule\StubRule;
use Exception;

/**
 * Provides canned answers to function or method invocations.
 *
 * @internal
 */
class Stub extends AbstractWrappedInvocable implements StubInterface
{
    /**
     * Construct a new stub.
     *
     * @param callable|null                    $callback           The callback, or null to create an unbound stub.
     * @param mixed                            $self               The self value.
     * @param string|null                      $label              The label.
     * @param MatcherFactoryInterface|null     $matcherFactory     The matcher factory to use.
     * @param MatcherVerifierInterface|null    $matcherVerifier    The matcher verifier to use.
     * @param InvokerInterface|null            $invoker            The invoker to use.
     * @param InvocableInspectorInterface|null $invocableInspector The invocable inspector to use.
     * @param FeatureDetectorInterface|null    $featureDetector    The feature detector to use.
     */
    public function __construct(
        $callback = null,
        $self = null,
        $label = null,
        MatcherFactoryInterface $matcherFactory = null,
        MatcherVerifierInterface $matcherVerifier = null,
        InvokerInterface $invoker = null,
        InvocableInspectorInterface $invocableInspector = null,
        FeatureDetectorInterface $featureDetector = null
    ) {
        if (null === $matcherFactory) {
            $matcherFactory = MatcherFactory::instance();
        }
        if (null === $matcherVerifier) {
            $matcherVerifier = MatcherVerifier::instance();
        }
        if (null === $invoker) {
            $invoker = Invoker::instance();
        }
        if (null === $invocableInspector) {
            $invocableInspector = InvocableInspector::instance();
        }
        if (null === $featureDetector) {
            $featureDetector = FeatureDetector::instance();
        }

        parent::__construct($callback, $label);

        if (null === $self) {
            $self = $this->callback;
        }

        $this->matcherFactory = $matcherFactory;
        $this->matcherVerifier = $matcherVerifier;
        $this->invoker = $invoker;
        $this->invocableInspector = $invocableInspector;
        $this->featureDetector = $featureDetector;

        $this->answer = new Answer();
        $this->isNewRule = false;
        $this->rules = array();
        $this->isClosureBindingSupported =
            $this->featureDetector->isSupported('closure.bind');

        $this->setSelf($self);
        $this->with($this->matcherFactory->wildcard());
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
     * Get the invoker.
     *
     * @return InvokerInterface The invoker.
     */
    public function invoker()
    {
        return $this->invoker;
    }

    /**
     * Get the invocable inspector.
     *
     * @return InvocableInspectorInterface The invocable inspector.
     */
    public function invocableInspector()
    {
        return $this->invocableInspector;
    }

    /**
     * Get the feature detector.
     *
     * @return FeatureDetectorInterface The feature detector.
     */
    public function featureDetector()
    {
        return $this->featureDetector;
    }

    /**
     * Set the self value of this stub.
     *
     * This value is used by returnsThis().
     *
     * @param mixed $self The self value.
     */
    public function setSelf($self)
    {
        $this->callback = $this->bind($this->callback, $self);

        if ($self === $this) {
            $self = null;
        }

        $this->self = $self;
    }

    /**
     * Get the self value of this stub.
     *
     * @return mixed The self value.
     */
    public function self()
    {
        if ($this->self) {
            return $this->self;
        }

        return $this;
    }

    /**
     * Modify the current criteria to match the supplied arguments.
     *
     * @param mixed $argument,... The arguments.
     *
     * @return StubInterface This stub.
     */
    public function with()
    {
        $this->handleDanglingRules();

        $this->isNewRule = true;
        $this->rule = new StubRule(
            $this->matcherFactory->adaptAll(func_get_args()),
            $this->matcherVerifier
        );

        return $this;
    }

    /**
     * Add a callback to be called as part of an answer.
     *
     * Note that all supplied callbacks will be called in the same invocation.
     *
     * @param callable $callback                The callback.
     * @param callable $additionalCallbacks,... Additional callbacks.
     *
     * @return StubInterface This stub.
     */
    public function calls($callback)
    {
        foreach (func_get_args() as $callback) {
            $this->callsWith($callback);
        }

        return $this;
    }

    /**
     * Add a callback to be called as part of an answer.
     *
     * This method supports reference parameters.
     *
     * Note that all supplied callbacks will be called in the same invocation.
     *
     * @param callable                                     $callback             The callback.
     * @param ArgumentsInterface|array<integer,mixed>|null $arguments            The arguments.
     * @param boolean|null                                 $prefixSelf           True if the self value should be prefixed.
     * @param boolean|null                                 $suffixArgumentsArray True if arguments should be appended as an array.
     * @param boolean|null                                 $suffixArguments      True if arguments should be appended.
     */
    public function callsWith(
        $callback,
        $arguments = null,
        $prefixSelf = null,
        $suffixArgumentsArray = null,
        $suffixArguments = null
    ) {
        $this->answer->addSecondaryRequest(
            new CallRequest(
                $this->bind($callback, $this->self),
                Arguments::adapt($arguments),
                $prefixSelf,
                $suffixArgumentsArray,
                $suffixArguments
            )
        );

        return $this;
    }

    /**
     * Add an argument callback to be called as part of an answer.
     *
     * Negative indices are equivalent to $argumentCount - $index.
     *
     * Note that all supplied callbacks will be called in the same invocation.
     *
     * @param integer|null $index                 The argument index, or null to call the first argument.
     * @param integer|null $additionalIndices,... Additional argument indices to call.
     *
     * @return StubInterface This stub.
     */
    public function callsArgument($index = null)
    {
        foreach (func_get_args() as $index) {
            $this->callsArgumentWith($index);
        }

        return $this;
    }

    /**
     * Add an argument callback to be called as part of an answer.
     *
     * Negative indices are equivalent to $argumentCount - $index.
     *
     * This method supports reference parameters in the supplied arguments, but
     * not in the invocation arguments.
     *
     * Note that all supplied callbacks will be called in the same invocation.
     *
     * @param integer|null                                 $index                The argument index, or null to call the first argument.
     * @param ArgumentsInterface|array<integer,mixed>|null $arguments            The arguments.
     * @param boolean|null                                 $prefixSelf           True if the self value should be prefixed.
     * @param boolean|null                                 $suffixArgumentsArray True if arguments should be appended as an array.
     * @param boolean|null                                 $suffixArguments      True if arguments should be appended.
     *
     * @return StubInterface This stub.
     */
    public function callsArgumentWith(
        $index = null,
        $arguments = null,
        $prefixSelf = null,
        $suffixArgumentsArray = null,
        $suffixArguments = null
    ) {
        if (null === $prefixSelf) {
            $prefixSelf = false;
        }
        if (null === $suffixArgumentsArray) {
            $suffixArgumentsArray = false;
        }
        if (null === $suffixArguments) {
            $suffixArguments = false;
        }

        $invoker = $this->invoker;
        $arguments = Arguments::adapt($arguments);

        return $this->callsWith(
            function ($self, $incoming) use (
                $invoker,
                $index,
                $arguments,
                $prefixSelf,
                $suffixArgumentsArray,
                $suffixArguments
            ) {
                if (!$incoming->has($index)) {
                    return;
                }

                $callback = $incoming->get($index);

                if (!is_callable($callback)) {
                    return;
                }

                $request = new CallRequest(
                    $callback,
                    $arguments,
                    $prefixSelf,
                    $suffixArgumentsArray,
                    $suffixArguments
                );
                $finalArguments = $request->finalArguments($self, $incoming);

                return $invoker->callWith($callback, $finalArguments);
            },
            null,
            true,
            true,
            false
        );
    }

    /**
     * Set the value of an argument passed by reference as part of an answer.
     *
     * If called with no arguments, sets the first argument to null.
     *
     * If called with one argument, sets the first argument to $indexOrValue.
     *
     * If called with two arguments, sets the argument at $indexOrValue to
     * $value.
     *
     * @param mixed $indexOrValue The index, or value if no index is specified.
     * @param mixed $value        The value.
     *
     * @return StubInterface This stub.
     */
    public function setsArgument($indexOrValue = null, $value = null)
    {
        if (func_num_args() > 1) {
            $index = $indexOrValue;
        } else {
            $index = 0;
            $normalized = 0;
            $value = $indexOrValue;
        }

        return $this->callsWith(
            function ($arguments) use ($index, $value) {
                if ($arguments->has($index)) {
                    $arguments->set($index, $value);
                }
            },
            null,
            false,
            true,
            false
        );

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
        foreach (func_get_args() as $callback) {
            $this->doesWith($callback);
        }

        return $this;
    }

    /**
     * Add a callback as an answer.
     *
     * @param callable                                     $callback             The callback.
     * @param ArgumentsInterface|array<integer,mixed>|null $arguments            The arguments.
     * @param boolean|null                                 $prefixSelf           True if the self value should be prefixed.
     * @param boolean|null                                 $suffixArgumentsArray True if arguments should be appended as an array.
     * @param boolean|null                                 $suffixArguments      True if arguments should be appended.
     *
     * @return StubInterface This stub.
     */
    public function doesWith(
        $callback,
        $arguments = null,
        $prefixSelf = null,
        $suffixArgumentsArray = null,
        $suffixArguments = null
    ) {
        if ($this->isNewRule) {
            $this->isNewRule = false;

            array_unshift($this->rules, $this->rule);
        }

        $this->answer->setPrimaryRequest(
            new CallRequest(
                $this->bind($callback, $this->self),
                Arguments::adapt($arguments),
                $prefixSelf,
                $suffixArgumentsArray,
                $suffixArguments
            )
        );
        $this->rule->addAnswer($this->answer);
        $this->answer = new Answer();

        return $this;
    }

    /**
     * Add an answer that calls the wrapped callback.
     *
     * @param ArgumentsInterface|array<integer,mixed>|null $arguments            The arguments.
     * @param boolean|null                                 $prefixSelf           True if the self value should be prefixed.
     * @param boolean|null                                 $suffixArgumentsArray True if arguments should be appended as an array.
     * @param boolean|null                                 $suffixArguments      True if arguments should be appended.
     *
     * @return StubInterface This stub.
     */
    public function forwards(
        $arguments = null,
        $prefixSelf = null,
        $suffixArgumentsArray = null,
        $suffixArguments = null
    ) {
        if (null === $prefixSelf) {
            $parameters = $this->invocableInspector
                ->callbackReflector($this->callback)->getParameters();

            $prefixSelf = $parameters &&
                'phonySelf' === $parameters[0]->getName();
        }

        $invoker = $this->invoker;
        $callback = $this->callback;
        $arguments = Arguments::adapt($arguments);

        return $this->doesWith(
            function ($self, $incoming) use (
                $invoker,
                $callback,
                $arguments,
                $prefixSelf,
                $suffixArgumentsArray,
                $suffixArguments
            ) {
                $request = new CallRequest(
                    $callback,
                    $arguments,
                    $prefixSelf,
                    $suffixArgumentsArray,
                    $suffixArguments
                );
                $finalArguments = $request->finalArguments($self, $incoming);

                return $invoker->callWith($callback, $finalArguments);
            },
            null,
            true,
            true,
            false
        );
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
            return $this->doesWith(function () {}, null, false, false, false);
        }

        foreach (func_get_args() as $value) {
            $this->doesWith(
                function () use ($value) {
                    return $value;
                },
                null,
                false,
                false,
                false
            );
        }

        return $this;
    }

    /**
     * Add an answer that returns an argument.
     *
     * Negative indices are equivalent to $argumentCount - $index.
     *
     * @param integer|null $index The argument index, or null to return the first argument.
     *
     * @return StubInterface This stub.
     */
    public function returnsArgument($index = null)
    {
        return $this->doesWith(
            function ($arguments) use ($index) {
                if ($arguments->has($index)) {
                    return $arguments->get($index);
                }
            },
            null,
            false,
            true,
            false
        );
    }

    /**
     * Add an answer that returns the self value.
     *
     * @return StubInterface This stub.
     */
    public function returnsSelf()
    {
        return $this->doesWith(
            function ($self) {
                return $self;
            },
            null,
            true,
            false,
            false
        );
    }

    /**
     * Add an answer that throws an exception.
     *
     * @param Exception|null $exception                The exception, or null to throw a generic exception.
     * @param Exception      $additionalExceptions,... Additional exceptions for subsequent invocations.
     *
     * @return StubInterface This stub.
     */
    public function throws(Exception $exception = null)
    {
        if (0 === func_num_args()) {
            return $this->doesWith(
                function () {
                    throw new Exception();
                },
                null,
                false,
                false,
                false
            );
        }

        foreach (func_get_args() as $exception) {
            $this->doesWith(
                function () use ($exception) {
                    throw $exception;
                },
                null,
                false,
                false,
                false
            );
        }

        return $this;
    }

    /**
     * Invoke this object.
     *
     * This method supports reference parameters.
     *
     * @param ArgumentsInterface|array<integer,mixed>|null The arguments.
     *
     * @return mixed     The result of invocation.
     * @throws Exception If an error occurs.
     */
    public function invokeWith($arguments = null)
    {
        $this->handleDanglingRules();

        $arguments = Arguments::adapt($arguments);

        foreach ($this->rules as $rule) {
            if ($rule->matches($arguments)) {
                break;
            }
        }

        $answer = $rule->next();

        foreach ($answer->secondaryRequests() as $request) {
            $this->invoker->callWith(
                $request->callback(),
                $request->finalArguments($this->self, $arguments)
            );
        }

        $request = $answer->primaryRequest();

        return $this->invoker->callWith(
            $request->callback(),
            $request->finalArguments($this->self, $arguments)
        );
    }

    /**
     * Handles any unfinished rules.
     */
    protected function handleDanglingRules()
    {
        if (!$this->rule) {
            return;
        }

        if (!$this->rules || $this->answer->secondaryRequests()) {
            $this->forwards();
        }
    }

    /**
     * Bind the supplied callback to the self value.
     *
     * @param callable $callback The callback.
     * @param mixed    $self     The self value.
     *
     * @return callable The bound callback.
     */
    protected function bind($callback, $self)
    {
        if ($this->isClosureBindingSupported) {
            if ($callback instanceof Closure) {
                if (is_object($self)) {
                    return $callback->bindTo($self, get_class($self));
                }

                if (is_string($self) && class_exists($self)) {
                    return $callback->bindTo(null, $self);
                }
            }
        }

        return $callback;
    }

    private $self;
    private $matcherFactory;
    private $matcherVerifier;
    private $invoker;
    private $invocableInspector;
    private $featureDetector;
    private $answer;
    private $isNewRule;
    private $rule;
    private $rules;
    private $isClosureBindingSupported;
}
