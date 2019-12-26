<?php

declare(strict_types=1);

namespace Eloquent\Phony\Stub;

use Eloquent\Phony\Call\Arguments;
use Eloquent\Phony\Exporter\Exporter;
use Eloquent\Phony\Invocation\InvocableInspector;
use Eloquent\Phony\Invocation\Invoker;
use Eloquent\Phony\Invocation\WrappedInvocableTrait;
use Eloquent\Phony\Matcher\Matcher;
use Eloquent\Phony\Matcher\MatcherFactory;
use Eloquent\Phony\Matcher\MatcherVerifier;
use Eloquent\Phony\Mock\Exception\FinalClassException;
use Eloquent\Phony\Mock\Handle\InstanceHandle;
use Eloquent\Phony\Mock\Method\WrappedCustomMethod;
use Eloquent\Phony\Stub\Answer\Answer;
use Eloquent\Phony\Stub\Answer\Builder\GeneratorAnswerBuilder;
use Eloquent\Phony\Stub\Answer\Builder\GeneratorAnswerBuilderFactory;
use Eloquent\Phony\Stub\Answer\CallRequest;
use Eloquent\Phony\Stub\Exception\FinalReturnTypeException;
use Eloquent\Phony\Stub\Exception\UnusedStubCriteriaException;
use Exception;
use ReflectionNamedType;
use Throwable;

/**
 * Provides canned answers to function or method invocations.
 */
class StubData implements Stub
{
    use WrappedInvocableTrait;

    /**
     * Creates a "forwards" answer on the supplied stub.
     *
     * @param Stub $stub The stub.
     */
    public static function forwardsAnswerCallback(Stub $stub): void
    {
        $stub->forwards();
    }

    /**
     * Creates an answer that returns an empty value on the supplied stub.
     *
     * @param Stub $stub The stub.
     */
    public static function returnsEmptyAnswerCallback(Stub $stub): void
    {
        $stub->returns();
    }

    /**
     * Construct a new stub data instance.
     *
     * @param ?callable                     $callback                      The callback, or null to create an anonymous stub.
     * @param string                        $label                         The label.
     * @param callable                      $defaultAnswerCallback         The callback to use when creating a default answer.
     * @param MatcherFactory                $matcherFactory                The matcher factory to use.
     * @param MatcherVerifier               $matcherVerifier               The matcher verifier to use.
     * @param Invoker                       $invoker                       The invoker to use.
     * @param InvocableInspector            $invocableInspector            The invocable inspector to use.
     * @param EmptyValueFactory             $emptyValueFactory             The empty value factory to use.
     * @param GeneratorAnswerBuilderFactory $generatorAnswerBuilderFactory The generator answer builder factory to use.
     * @param Exporter                      $exporter                      The exporter to use.
     */
    public function __construct(
        ?callable $callback,
        string $label,
        callable $defaultAnswerCallback,
        MatcherFactory $matcherFactory,
        MatcherVerifier $matcherVerifier,
        Invoker $invoker,
        InvocableInspector $invocableInspector,
        EmptyValueFactory $emptyValueFactory,
        GeneratorAnswerBuilderFactory $generatorAnswerBuilderFactory,
        Exporter $exporter
    ) {
        if (!$callback) {
            $this->isAnonymous = true;
            $this->callback = function () {};
        } else {
            $this->isAnonymous = false;
            $this->callback = $callback;
        }

        $this->self = $this;
        $this->label = $label;
        $this->defaultAnswerCallback = $defaultAnswerCallback;
        $this->matcherFactory = $matcherFactory;
        $this->matcherVerifier = $matcherVerifier;
        $this->invoker = $invoker;
        $this->invocableInspector = $invocableInspector;
        $this->emptyValueFactory = $emptyValueFactory;
        $this->generatorAnswerBuilderFactory = $generatorAnswerBuilderFactory;
        $this->exporter = $exporter;

        $this->secondaryRequests = [];
        $this->answers = [];
        $this->rules = [];
    }

    /**
     * Get the default answer callback.
     *
     * @return callable The default answer callback.
     */
    public function defaultAnswerCallback(): callable
    {
        return $this->defaultAnswerCallback;
    }

    /**
     * Set the self value of this stub.
     *
     * This value is used by returnsThis().
     *
     * @param mixed $self The self value.
     *
     * @return $this This stub.
     */
    public function setSelf($self): Stub
    {
        $this->self = $self;

        return $this;
    }

    /**
     * Get the self value of this stub.
     *
     * @return mixed The self value.
     */
    public function self()
    {
        return $this->self;
    }

    /**
     * Set the callback to use when creating a default answer.
     *
     * @param callable $defaultAnswerCallback The default answer callback.
     *
     * @return $this This stub.
     */
    public function setDefaultAnswerCallback(
        callable $defaultAnswerCallback
    ): Stub {
        $this->defaultAnswerCallback = $defaultAnswerCallback;

        return $this;
    }

    /**
     * Modify the current criteria to match the supplied arguments.
     *
     * @param mixed ...$arguments The arguments.
     *
     * @return $this This stub.
     */
    public function with(...$arguments): Stub
    {
        $this->closeRule();

        if (empty($this->rules)) {
            $defaultAnswerCallback = $this->defaultAnswerCallback;
            $defaultAnswerCallback($this);
            $this->closeRule();
        }

        $this->criteria = $this->matcherFactory->adaptAll($arguments);

        return $this;
    }

    /**
     * Add a callback to be called as part of an answer.
     *
     * Note that all supplied callbacks will be called in the same invocation.
     *
     * @param callable ...$callbacks The callbacks.
     *
     * @return $this This stub.
     */
    public function calls(callable ...$callbacks): Stub
    {
        foreach ($callbacks as $callback) {
            $this->callsWith($callback);
        }

        return $this;
    }

    /**
     * Add a callback to be called as part of an answer.
     *
     * Note that all supplied callbacks will be called in the same invocation.
     *
     * @param callable                   $callback              The callback.
     * @param Arguments|array<int,mixed> $arguments             The arguments.
     * @param ?bool                      $prefixSelf            True if the self value should be prefixed.
     * @param bool                       $suffixArgumentsObject True if the arguments object should be appended.
     * @param bool                       $suffixArguments       True if the arguments should be appended individually.
     *
     * @return $this This stub.
     */
    public function callsWith(
        callable $callback,
        $arguments = [],
        bool $prefixSelf = null,
        bool $suffixArgumentsObject = false,
        bool $suffixArguments = true
    ): Stub {
        if (null === $prefixSelf) {
            $parameters = $this->invocableInspector
                ->callbackReflector($callback)->getParameters();

            $prefixSelf = !empty($parameters) &&
                'phonySelf' === $parameters[0]->getName();
        }

        if (!$arguments instanceof Arguments) {
            $arguments = new Arguments($arguments);
        }

        $this->secondaryRequests[] = new CallRequest(
            $callback,
            $arguments,
            $prefixSelf,
            $suffixArgumentsObject,
            $suffixArguments
        );

        return $this;
    }

    /**
     * Add an argument callback to be called as part of an answer.
     *
     * Calling this method with no arguments is equivalent to calling it with a
     * single argument of `0`.
     *
     * Negative indices are offset from the end of the list. That is, `-1`
     * indicates the last element, and `-2` indicates the second last element.
     *
     * Note that all supplied callbacks will be called in the same invocation.
     *
     * @param int ...$indices The argument indices.
     *
     * @return $this This stub.
     */
    public function callsArgument(int ...$indices): Stub
    {
        if (empty($indices)) {
            $this->callsArgumentWith(0);
        } else {
            foreach ($indices as $index) {
                $this->callsArgumentWith($index);
            }
        }

        return $this;
    }

    /**
     * Add an argument callback to be called as part of an answer.
     *
     * Negative indices are offset from the end of the list. That is, `-1`
     * indicates the last element, and `-2` indicates the second last element.
     *
     * Note that all supplied callbacks will be called in the same invocation.
     *
     * @param int                        $index                 The argument index.
     * @param Arguments|array<int,mixed> $arguments             The arguments.
     * @param bool                       $prefixSelf            True if the self value should be prefixed.
     * @param bool                       $suffixArgumentsObject True if the arguments object should be appended.
     * @param bool                       $suffixArguments       True if the arguments should be appended individually.
     *
     * @return $this This stub.
     */
    public function callsArgumentWith(
        int $index = 0,
        $arguments = [],
        bool $prefixSelf = false,
        bool $suffixArgumentsObject = false,
        bool $suffixArguments = false
    ): Stub {
        $invoker = $this->invoker;

        if (!$arguments instanceof Arguments) {
            $arguments = new Arguments($arguments);
        }

        return $this->callsWith(
            function ($self, $incoming) use (
                $invoker,
                $index,
                $arguments,
                $prefixSelf,
                $suffixArgumentsObject,
                $suffixArguments
            ) {
                $callback = $incoming->get($index);

                $request = new CallRequest(
                    $callback,
                    $arguments,
                    $prefixSelf,
                    $suffixArgumentsObject,
                    $suffixArguments
                );
                $finalArguments = $request->finalArguments($self, $incoming);

                return $invoker->callWith($callback, $finalArguments);
            },
            [],
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
     * @return $this This stub.
     */
    public function setsArgument($indexOrValue = null, $value = null): Stub
    {
        if (func_num_args() > 1) {
            $index = $indexOrValue;
        } else {
            $index = 0;
            $value = $indexOrValue;
        }

        if ($value instanceof InstanceHandle) {
            $value = $value->get();
        }

        return $this->callsWith(
            function ($arguments) use ($index, $value) {
                $arguments->set($index, $value);
            },
            [],
            false,
            true,
            false
        );
    }

    /**
     * Add a callback as an answer.
     *
     * @param callable ...$callbacks The callbacks.
     *
     * @return $this This stub.
     */
    public function does(callable ...$callbacks): Stub
    {
        foreach ($callbacks as $callback) {
            $this->doesWith($callback);
        }

        return $this;
    }

    /**
     * Add a callback as an answer.
     *
     * @param callable                   $callback              The callback.
     * @param Arguments|array<int,mixed> $arguments             The arguments.
     * @param ?bool                      $prefixSelf            True if the self value should be prefixed.
     * @param bool                       $suffixArgumentsObject True if the arguments object should be appended.
     * @param bool                       $suffixArguments       True if the arguments should be appended individually.
     *
     * @return $this This stub.
     */
    public function doesWith(
        callable $callback,
        $arguments = [],
        bool $prefixSelf = null,
        bool $suffixArgumentsObject = false,
        bool $suffixArguments = true
    ): Stub {
        if (null === $prefixSelf) {
            $parameters = $this->invocableInspector
                ->callbackReflector($callback)->getParameters();

            $prefixSelf = !empty($parameters) &&
                'phonySelf' === $parameters[0]->getName();
        }

        if (!$arguments instanceof Arguments) {
            $arguments = new Arguments($arguments);
        }

        $this->answers[] = new Answer(
            new CallRequest(
                $callback,
                $arguments,
                $prefixSelf,
                $suffixArgumentsObject,
                $suffixArguments
            ),
            $this->secondaryRequests
        );
        $this->secondaryRequests = [];

        return $this;
    }

    /**
     * Add an answer that calls the wrapped callback.
     *
     * @param Arguments|array<int,mixed> $arguments             The arguments.
     * @param ?bool                      $prefixSelf            True if the self value should be prefixed.
     * @param bool                       $suffixArgumentsObject True if the arguments object should be appended.
     * @param bool                       $suffixArguments       True if the arguments should be appended individually.
     *
     * @return $this This stub.
     */
    public function forwards(
        $arguments = [],
        bool $prefixSelf = null,
        bool $suffixArgumentsObject = false,
        bool $suffixArguments = true
    ): Stub {
        if (null === $prefixSelf) {
            if ($this->callback instanceof WrappedCustomMethod) {
                $parameters = $this->invocableInspector
                    ->callbackReflector($this->callback->customCallback())
                    ->getParameters();
            } else {
                /** @var callable */
                $callback = $this->callback;
                $parameters = $this->invocableInspector
                    ->callbackReflector($callback)->getParameters();
            }

            $prefixSelf = !empty($parameters) &&
                'phonySelf' === $parameters[0]->getName();
        }

        $invoker = $this->invoker;
        /** @var callable */
        $callback = $this->callback;

        if (!$arguments instanceof Arguments) {
            $arguments = new Arguments($arguments);
        }

        return $this->doesWith(
            function ($self, $incoming) use (
                $invoker,
                $callback,
                $arguments,
                $prefixSelf,
                $suffixArgumentsObject,
                $suffixArguments
            ) {
                $request = new CallRequest(
                    $callback,
                    $arguments,
                    $prefixSelf,
                    $suffixArgumentsObject,
                    $suffixArguments
                );
                $finalArguments = $request->finalArguments($self, $incoming);

                return $invoker->callWith($callback, $finalArguments);
            },
            [],
            true,
            true,
            false
        );
    }

    /**
     * Add an answer that returns a value.
     *
     * Calling this method with no arguments is equivalent to calling it with a
     * single argument of `null`.
     *
     * @param mixed ...$values The return values.
     *
     * @return $this This stub.
     */
    public function returns(...$values): Stub
    {
        if (empty($values)) {
            /** @var callable */
            $callback = $this->callback;
            $invocableInspector = $this->invocableInspector;
            $emptyValueFactory = $this->emptyValueFactory;

            $value = null;
            $valueIsSet = false;

            return $this->doesWith(
                function () use (
                    &$value,
                    &$valueIsSet,
                    $callback,
                    $invocableInspector,
                    $emptyValueFactory
                ) {
                    if (!$valueIsSet) {
                        if (
                            $type = $invocableInspector
                                ->callbackReflector($callback)->getReturnType()
                        ) {
                            try {
                                $value = $emptyValueFactory->fromType($type);
                            } catch (FinalClassException $e) {
                                /** @var ReflectionNamedType */
                                $namedType = $type;

                                throw new FinalReturnTypeException(
                                    $this->exporter->exportCallable($callback),
                                    $namedType->getName(),
                                    $e
                                );
                            }
                        } else {
                            $value = null;
                        }

                        $valueIsSet = true;
                    }

                    return $value;
                },
                [],
                false,
                false,
                false
            );
        }

        foreach ($values as $value) {
            if ($value instanceof InstanceHandle) {
                $value = $value->get();
            }

            $this->doesWith(
                function () use ($value) {
                    return $value;
                },
                [],
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
     * Negative indices are offset from the end of the list. That is, `-1`
     * indicates the last element, and `-2` indicates the second last element.
     *
     * @param int $index The argument index.
     *
     * @return $this This stub.
     */
    public function returnsArgument(int $index = 0): Stub
    {
        return $this->doesWith(
            function ($arguments) use ($index) {
                return $arguments->get($index);
            },
            [],
            false,
            true,
            false
        );
    }

    /**
     * Add an answer that returns the self value.
     *
     * @return $this This stub.
     */
    public function returnsSelf(): Stub
    {
        return $this->doesWith(
            function ($self) {
                return $self;
            },
            [],
            true,
            false,
            false
        );
    }

    /**
     * Add an answer that throws an exception.
     *
     * Calling this method with no arguments is equivalent to calling it with a
     * single argument of `null`.
     *
     * @param Throwable|string|null ...$exceptions The exceptions, or messages, or nulls to throw generic exceptions.
     *
     * @return $this This stub.
     */
    public function throws(...$exceptions): Stub
    {
        if (empty($exceptions)) {
            return $this->doesWith(
                function () {
                    throw new Exception();
                },
                [],
                false,
                false,
                false
            );
        }

        foreach ($exceptions as $exception) {
            if (null === $exception) {
                $exception = new Exception();
            } elseif (is_string($exception)) {
                $exception = new Exception($exception);
            } elseif ($exception instanceof InstanceHandle) {
                /** @var Throwable */
                $exception = $exception->get();
            }

            $this->doesWith(
                function () use ($exception) {
                    throw $exception;
                },
                [],
                false,
                false,
                false
            );
        }

        return $this;
    }

    /**
     * Add an answer that returns a generator, and return a builder for
     * customizing the generator's behavior.
     *
     * @param iterable<mixed> ...$values Sets of keys and values to yield.
     *
     * @return GeneratorAnswerBuilder The answer builder.
     */
    public function generates(iterable ...$values): GeneratorAnswerBuilder
    {
        $builder = $this->generatorAnswerBuilderFactory->create($this);
        $this->doesWith($builder->answer(), [], true, true, false);

        foreach ($values as $index => $subValues) {
            if ($index > 0) {
                $builder->returns();

                $builder = $this->generatorAnswerBuilderFactory->create($this);
                $this->doesWith($builder->answer(), [], true, true, false);
            }

            $builder->yieldsFrom($subValues);
        }

        return $builder;
    }

    /**
     * Close any existing rule.
     *
     * @return $this This stub.
     */
    public function closeRule(): Stub
    {
        if (!empty($this->secondaryRequests)) {
            $defaultAnswerCallback = $this->defaultAnswerCallback;
            $defaultAnswerCallback($this);
            $this->secondaryRequests = [];
        }

        if (!empty($this->answers)) {
            if (null !== $this->criteria) {
                $rule = new StubRule($this->criteria, $this->answers);

                $this->criteria = null;
            } else {
                $rule = new StubRule(
                    [$this->matcherFactory->wildcard()],
                    $this->answers
                );
            }

            array_unshift($this->rules, $rule);
            $this->answers = [];
        }

        if (null !== $this->criteria) {
            $criteria = $this->criteria;
            $this->criteria = null;

            throw new UnusedStubCriteriaException($criteria);
        }

        return $this;
    }

    /**
     * Invoke this object.
     *
     * This method supports reference parameters.
     *
     * @param Arguments|array<int,mixed> $arguments The arguments.
     *
     * @return mixed     The result of invocation.
     * @throws Throwable If an error occurs.
     */
    public function invokeWith($arguments = [])
    {
        $this->closeRule();

        if (empty($this->rules)) {
            $defaultAnswerCallback = $this->defaultAnswerCallback;
            $defaultAnswerCallback($this);
            $this->closeRule();
        }

        if ($arguments instanceof Arguments) {
            $argumentsArray = $arguments->all();
        } else {
            $argumentsArray = $arguments;
            $arguments = new Arguments($arguments);
        }

        $rule = null;

        foreach ($this->rules as $rule) {
            if (
                $this->matcherVerifier
                    ->matches($rule->criteria(), $argumentsArray)
            ) {
                break;
            }
        }

        assert($rule instanceof StubRule);
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
     * Limits the output displayed when `var_dump` is used.
     *
     * @return array<string,mixed> The contents to export.
     */
    public function __debugInfo(): array
    {
        return ['label' => $this->label];
    }

    /**
     * @var mixed
     */
    private $self;

    /**
     * @var callable
     */
    private $defaultAnswerCallback;

    /**
     * @var MatcherFactory
     */
    private $matcherFactory;

    /**
     * @var MatcherVerifier
     */
    private $matcherVerifier;

    /**
     * @var Invoker
     */
    private $invoker;

    /**
     * @var InvocableInspector
     */
    private $invocableInspector;

    /**
     * @var EmptyValueFactory
     */
    private $emptyValueFactory;

    /**
     * @var GeneratorAnswerBuilderFactory
     */
    private $generatorAnswerBuilderFactory;

    /**
     * @var Exporter
     */
    private $exporter;

    /**
     * @var ?array<int,Matcher>
     */
    private $criteria;

    /**
     * @var array<int,CallRequest>
     */
    private $secondaryRequests;

    /**
     * @var array<int,Answer>
     */
    private $answers;

    /**
     * @var array<int,StubRule>
     */
    private $rules;
}
