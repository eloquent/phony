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
use ReflectionClass;
use ReflectionException;

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
     * @param integer|null                     $id                 The identifier.
     * @param MatcherFactoryInterface|null     $matcherFactory     The matcher factory to use.
     * @param MatcherVerifierInterface|null    $matcherVerifier    The matcher verifier to use.
     * @param InvokerInterface|null            $invoker            The invoker to use.
     * @param InvocableInspectorInterface|null $invocableInspector The invocable inspector to use.
     */
    public function __construct(
        $callback = null,
        $self = null,
        $id = null,
        MatcherFactoryInterface $matcherFactory = null,
        MatcherVerifierInterface $matcherVerifier = null,
        InvokerInterface $invoker = null,
        InvocableInspectorInterface $invocableInspector = null
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

        parent::__construct($callback, $id);

        if (null === $self) {
            $self = $this->callback;
        }

        $this->self = $self;
        $this->matcherFactory = $matcherFactory;
        $this->matcherVerifier = $matcherVerifier;
        $this->invoker = $invoker;
        $this->invocableInspector = $invocableInspector;

        $this->answer = new Answer();
        $this->isNewRule = true;
        $this->rule = new StubRule(
            array($this->matcherFactory->wildcard()),
            $this->matcherVerifier
        );
        $this->rules = array();
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
     * Set the self value of this stub.
     *
     * This value is used by returnsThis().
     *
     * @param mixed $self The self value.
     */
    public function setSelf($self)
    {
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
     * Modify the current criteria to match the supplied arguments (and possibly
     * others).
     *
     * @param mixed $argument,... The arguments.
     *
     * @return StubInterface This stub.
     */
    public function with()
    {
        if ($this->isNewRule) {
            $this->forwards();
        }

        $this->isNewRule = true;
        $matchers = $this->matcherFactory->adaptAll(func_get_args());
        $matchers[] = $this->matcherFactory->wildcard();
        $this->rule = new StubRule($matchers, $this->matcherVerifier);

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
        if ($this->isNewRule) {
            $this->forwards();
        }

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
     * @param callable                  $callback             The callback.
     * @param array<integer,mixed>|null $arguments            The arguments.
     * @param boolean|null              $prefixSelf           True if the self value should be prefixed.
     * @param boolean|null              $suffixArgumentsArray True if arguments should be appended as an array.
     * @param boolean|null              $suffixArguments      True if arguments should be appended.
     */
    public function callsWith(
        $callback,
        array $arguments = null,
        $prefixSelf = null,
        $suffixArgumentsArray = null,
        $suffixArguments = null
    ) {
        $this->answer->addSecondaryRequest(
            new CallRequest(
                $callback,
                $arguments,
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
     * @param integer|null              $index                The argument index, or null to call the first argument.
     * @param array<integer,mixed>|null $arguments            The arguments.
     * @param boolean|null              $prefixSelf           True if the self value should be prefixed.
     * @param boolean|null              $suffixArgumentsArray True if arguments should be appended as an array.
     * @param boolean|null              $suffixArguments      True if arguments should be appended.
     *
     * @return StubInterface This stub.
     */
    public function callsArgumentWith(
        $index = null,
        array $arguments = null,
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

        $indexNormalizer = $this->indexNormalizer();
        $invoker = $this->invoker;

        return $this->callsWith(
            function ($self, array $incoming) use (
                $indexNormalizer,
                $invoker,
                $index,
                $arguments,
                $prefixSelf,
                $suffixArgumentsArray,
                $suffixArguments
            ) {
                $index = $indexNormalizer($index, count($incoming));

                if (null === $index) {
                    return;
                }

                $callback = $incoming[$index];

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
     * @param mixed        $value The value to set the argument to.
     * @param integer|null $index The argument index, or null to set the first argument.
     *
     * @return StubInterface This stub.
     */
    public function setsArgument($value, $index = null)
    {
        $indexNormalizer = $this->indexNormalizer();

        return $this->callsWith(
            function (array $arguments) use ($indexNormalizer, $value, $index) {
                $index = $indexNormalizer($index, count($arguments));

                if (null === $index) {
                    return;
                }

                $arguments[$index] = $value;
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
     * @param callable                  $callback             The callback.
     * @param array<integer,mixed>|null $arguments            The arguments.
     * @param boolean|null              $prefixSelf           True if the self value should be prefixed.
     * @param boolean|null              $suffixArgumentsArray True if arguments should be appended as an array.
     * @param boolean|null              $suffixArguments      True if arguments should be appended.
     *
     * @return StubInterface This stub.
     */
    public function doesWith(
        $callback,
        array $arguments = null,
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
                $callback,
                $arguments,
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
     * @param array<integer,mixed>|null $arguments            The arguments.
     * @param boolean|null              $prefixSelf           True if the self value should be prefixed.
     * @param boolean|null              $suffixArgumentsArray True if arguments should be appended as an array.
     * @param boolean|null              $suffixArguments      True if arguments should be appended.
     *
     * @return StubInterface This stub.
     */
    public function forwards(
        array $arguments = null,
        $prefixSelf = null,
        $suffixArgumentsArray = null,
        $suffixArguments = null
    ) {
        if (
            null === $prefixSelf &&
            is_object($this->callback) &&
            is_object($this->self)
        ) {
            $selfClass = new ReflectionClass($this->self);
            $parameters = $this->invocableInspector
                ->callbackReflector($this->callback)->getParameters();

            if ($parameters && 'self' === $parameters[0]->getName()) {
                try {
                    if ($parameterClass = $parameters[0]->getClass()) {
                        $parameterClassName = $parameterClass->getName();

                        if (
                            $selfClass->getName() === $parameterClassName ||
                            $selfClass->isSubclassOf($parameterClassName)
                        ) {
                            $prefixSelf = true;
                        }
                    }
                } catch (ReflectionException $e) {
                    // ignore
                }
            }
        }

        $invoker = $this->invoker;
        $callback = $this->callback;

        return $this->doesWith(
            function ($self, array $incoming) use (
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
        $indexNormalizer = $this->indexNormalizer();

        return $this->doesWith(
            function (array $arguments) use ($indexNormalizer, $index) {
                $index = $indexNormalizer($index, count($arguments));

                if (null === $index) {
                    return null;
                }

                return $arguments[$index];
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
     * @param array<integer,mixed>|null The arguments.
     *
     * @return mixed     The result of invocation.
     * @throws Exception If an error occurs.
     */
    public function invokeWith(array $arguments = null)
    {
        if (null === $arguments) {
            $arguments = array();
        }

        if ($this->isNewRule) {
            $this->forwards();
        }

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
     * Returns a callback to use when normalizing indices.
     *
     * @return callable The index normalizer.
     */
    protected function indexNormalizer()
    {
        return function ($index, $count) {
            if ($count < 1) {
                return null;
            }

            if (null === $index) {
                $index = 0;
            } elseif ($index < 0) {
                $index = $count + $index;

                if ($index < 0) {
                    return null;
                }
            }

            if ($index >= $count) {
                return null;
            }

            return $index;
        };
    }

    private $self;
    private $matcherFactory;
    private $matcherVerifier;
    private $invoker;
    private $invocableInspector;
    private $answer;
    private $isNewRule;
    private $rule;
    private $rules;
}
