<?php

declare(strict_types=1);

namespace Eloquent\Phony\Mock\Handle;

use Eloquent\Phony\Assertion\AssertionRecorder;
use Eloquent\Phony\Assertion\AssertionRenderer;
use Eloquent\Phony\Event\EventCollection;
use Eloquent\Phony\Invocation\Invoker;
use Eloquent\Phony\Mock\Exception\FinalClassException;
use Eloquent\Phony\Mock\Exception\FinalMethodStubException;
use Eloquent\Phony\Mock\Exception\UndefinedMethodStubException;
use Eloquent\Phony\Mock\Method\WrappedCustomMethod;
use Eloquent\Phony\Mock\Method\WrappedMagicMethod;
use Eloquent\Phony\Mock\Method\WrappedParentMethod;
use Eloquent\Phony\Mock\Method\WrappedTraitMethod;
use Eloquent\Phony\Mock\Method\WrappedUncallableMethod;
use Eloquent\Phony\Mock\Mock;
use Eloquent\Phony\Spy\Spy;
use Eloquent\Phony\Stub\EmptyValueFactory;
use Eloquent\Phony\Stub\StubData;
use Eloquent\Phony\Stub\StubFactory;
use Eloquent\Phony\Stub\StubVerifier;
use Eloquent\Phony\Stub\StubVerifierFactory;
use ReflectionClass;
use ReflectionMethod;
use stdClass;
use Throwable;

/**
 * Used for implementing handles.
 */
trait HandleTrait
{
    /**
     * Get the class.
     *
     * @return ReflectionClass<object> The class.
     */
    public function class(): ReflectionClass
    {
        return $this->class;
    }

    /**
     * Get the class name.
     *
     * @return string The class name.
     */
    public function className(): string
    {
        return $this->class->getName();
    }

    /**
     * Turn the mock into a full mock.
     *
     * @return $this This handle.
     */
    public function full(): Handle
    {
        $this->state->defaultAnswerCallback =
            [StubData::class, 'returnsEmptyAnswerCallback'];

        return $this;
    }

    /**
     * Turn the mock into a partial mock.
     *
     * @return $this This handle.
     */
    public function partial(): Handle
    {
        $this->state->defaultAnswerCallback =
            [StubData::class, 'forwardsAnswerCallback'];

        return $this;
    }

    /**
     * Set the callback to use when creating a default answer.
     *
     * @param callable $defaultAnswerCallback The default answer callback.
     *
     * @return $this This handle.
     */
    public function setDefaultAnswerCallback(
        callable $defaultAnswerCallback
    ): Handle {
        $this->state->defaultAnswerCallback = $defaultAnswerCallback;

        return $this;
    }

    /**
     * Get the default answer callback.
     *
     * @return callable The default answer callback.
     */
    public function defaultAnswerCallback(): callable
    {
        return $this->state->defaultAnswerCallback;
    }

    /**
     * Get a stub verifier.
     *
     * @param string $name      The method name.
     * @param bool   $isNewRule True if a new rule should be started.
     *
     * @return StubVerifier  The stub verifier.
     * @throws MockException If the stub does not exist.
     */
    public function stub(string $name, bool $isNewRule = true): StubVerifier
    {
        $key = strtolower($name);

        if (isset($this->state->stubs->$key)) {
            $stub = $this->state->stubs->$key;
        } else {
            $stub = $this->state->stubs->$key = $this->createStub($name);
        }

        if ($isNewRule) {
            $stub->closeRule();
        }

        return $stub;
    }

    /**
     * Get a stub verifier.
     *
     * Using this method will always start a new rule.
     *
     * @param string $name The method name.
     *
     * @return StubVerifier  The stub verifier.
     * @throws MockException If the stub does not exist.
     */
    public function __get(string $name): StubVerifier
    {
        $key = strtolower($name);

        if (isset($this->state->stubs->$key)) {
            $stub = $this->state->stubs->$key;
        } else {
            $stub = $this->state->stubs->$key = $this->createStub($name);
        }

        return $stub->closeRule();
    }

    /**
     * Get a spy.
     *
     * @param string $name The method name.
     *
     * @return Spy           The spy.
     * @throws MockException If the spy does not exist.
     */
    public function spy(string $name): Spy
    {
        return $this->stub($name)->spy();
    }

    /**
     * Checks if there was no interaction with the mock.
     *
     * @return ?EventCollection The result.
     */
    public function checkNoInteraction(): ?EventCollection
    {
        foreach (get_object_vars($this->state->stubs) as $stub) {
            if ($stub->checkCalled()) {
                return null;
            }
        }

        return $this->assertionRecorder->createSuccess();
    }

    /**
     * Record an assertion failure unless there was no interaction with the mock.
     *
     * @return ?EventCollection The result, or null if the assertion recorder does not throw exceptions.
     * @throws Throwable        If the assertion fails, and the assertion recorder throws exceptions.
     */
    public function noInteraction(): ?EventCollection
    {
        if ($result = $this->checkNoInteraction()) {
            return $result;
        }

        $calls = [];

        foreach (get_object_vars($this->state->stubs) as $stub) {
            $calls = array_merge($calls, $stub->allCalls());
        }

        return $this->assertionRecorder->createFailure(
            $this->assertionRenderer->renderNoInteraction($this, $calls)
        );
    }

    /**
     * Stop recording calls.
     *
     * @return $this This handle.
     */
    public function stopRecording(): Handle
    {
        foreach (get_object_vars($this->state->stubs) as $stub) {
            $stub->stopRecording();
        }

        $this->state->isRecording = false;

        return $this;
    }

    /**
     * Start recording calls.
     *
     * @return $this This handle.
     */
    public function startRecording(): Handle
    {
        foreach (get_object_vars($this->state->stubs) as $stub) {
            $stub->startRecording();
        }

        $this->state->isRecording = true;

        return $this;
    }

    /**
     * Get the handle state.
     *
     * @return stdClass The state.
     */
    public function state(): stdClass
    {
        return $this->state;
    }

    private function createStub(string $name): StubVerifier
    {
        $isMagic = !$this->class->hasMethod($name);
        $callMagicMethod = $this->callMagicMethod;

        if ($isMagic && !$callMagicMethod) {
            throw new UndefinedMethodStubException(
                $this->class->getName(),
                $name
            );
        }

        $mock = $this->mock;
        $key = strtolower($name);

        if ($isMagic) {
            if ($mock) {
                $magicKey = '__call';
            } else {
                $magicKey = '__callstatic';
            }

            if (isset($this->uncallableMethods[$magicKey])) {
                $isUncallable = true;

                try {
                    $returnValue = $this->emptyValueFactory->fromFunction(
                        $this->class->getMethod($magicKey)
                    );
                    $exception = null;
                } catch (FinalClassException $e) {
                    $returnValue = null;
                    $exception = $e;
                }
            } else {
                $isUncallable = false;
                $returnValue = null;
                $exception = null;
            }

            assert($this->callMagicMethod instanceof ReflectionMethod);

            $stub = $this->stubFactory->create(
                new WrappedMagicMethod(
                    $this->callMagicMethod,
                    $this->class->getMethod($magicKey),
                    $name,
                    $isUncallable,
                    $this,
                    $exception,
                    $returnValue
                ),
                $this->state->defaultAnswerCallback
            );
        } elseif (isset($this->uncallableMethods[$key])) {
            $method = $this->class->getMethod($name);

            try {
                $returnValue = $this->emptyValueFactory->fromFunction($method);
                $exception = null;
            } catch (FinalClassException $e) {
                $returnValue = null;
                $exception = $e;
            }

            $stub = $this->stubFactory->create(
                new WrappedUncallableMethod(
                    $method,
                    $this,
                    $exception,
                    $returnValue
                ),
                $this->state->defaultAnswerCallback
            );
        } elseif (isset($this->traitMethods[$key])) {
            assert($this->callTraitMethod instanceof ReflectionMethod);

            $stub = $this->stubFactory->create(
                new WrappedTraitMethod(
                    $this->callTraitMethod,
                    $this->class->getMethod($name),
                    $this->traitMethods[$key],
                    $this
                ),
                $this->state->defaultAnswerCallback
            );
        } elseif (array_key_exists($key, $this->customMethods)) {
            $stub = $this->stubFactory->create(
                new WrappedCustomMethod(
                    $this->customMethods[$key],
                    $this->class->getMethod($name),
                    $this,
                    $this->invoker
                ),
                $this->state->defaultAnswerCallback
            );
        } else {
            $method = $this->class->getMethod($name);

            if ($method->isFinal()) {
                throw new FinalMethodStubException(
                    $this->class->getName(),
                    $name
                );
            }

            assert($this->callParentMethod instanceof ReflectionMethod);

            $stub = $this->stubFactory->create(
                new WrappedParentMethod(
                    $this->callParentMethod,
                    $method,
                    $this
                ),
                $this->state->defaultAnswerCallback
            );
        }

        $stubVerifier = $this->stubVerifierFactory->create($stub, $mock);

        if (!$this->state->isRecording) {
            $stubVerifier->stopRecording();
        }

        return $stubVerifier;
    }

    /**
     * @param ReflectionClass<object> $class
     */
    private function constructHandle(
        ReflectionClass $class,
        stdClass $state,
        ?ReflectionMethod $callParentMethod,
        ?ReflectionMethod $callTraitMethod,
        ?ReflectionMethod $callMagicMethod,
        ?Mock $mock,
        StubFactory $stubFactory,
        StubVerifierFactory $stubVerifierFactory,
        EmptyValueFactory $emptyValueFactory,
        AssertionRenderer $assertionRenderer,
        AssertionRecorder $assertionRecorder,
        Invoker $invoker
    ): void {
        $this->mock = $mock;
        $this->class = $class;
        $this->state = $state;
        $this->callParentMethod = $callParentMethod;
        $this->callTraitMethod = $callTraitMethod;
        $this->callMagicMethod = $callMagicMethod;
        $this->stubFactory = $stubFactory;
        $this->stubVerifierFactory = $stubVerifierFactory;
        $this->emptyValueFactory = $emptyValueFactory;
        $this->assertionRenderer = $assertionRenderer;
        $this->assertionRecorder = $assertionRecorder;
        $this->invoker = $invoker;

        $uncallableMethodsProperty = $class->getProperty('_uncallableMethods');
        $uncallableMethodsProperty->setAccessible(true);
        $this->uncallableMethods = $uncallableMethodsProperty->getValue();

        $traitMethodsProperty = $class->getProperty('_traitMethods');
        $traitMethodsProperty->setAccessible(true);
        $this->traitMethods = $traitMethodsProperty->getValue();

        $customMethodsProperty = $class->getProperty('_customMethods');
        $customMethodsProperty->setAccessible(true);
        $this->customMethods = $customMethodsProperty->getValue();
    }

    /**
     * @var stdClass
     */
    private $state;

    /**
     * @var ReflectionClass<object>
     */
    private $class;

    /**
     * @var ?Mock
     */
    private $mock;

    /**
     * @var array<string,bool>
     */
    private $uncallableMethods;

    /**
     * @var array<string,class-string>
     */
    private $traitMethods;

    /**
     * @var ?ReflectionMethod
     */
    private $callParentMethod;

    /**
     * @var ?ReflectionMethod
     */
    private $callTraitMethod;

    /**
     * @var ?ReflectionMethod
     */
    private $callMagicMethod;

    /**
     * @var StubFactory
     */
    private $stubFactory;

    /**
     * @var StubVerifierFactory
     */
    private $stubVerifierFactory;

    /**
     * @var EmptyValueFactory
     */
    private $emptyValueFactory;

    /**
     * @var AssertionRenderer
     */
    private $assertionRenderer;

    /**
     * @var AssertionRecorder
     */
    private $assertionRecorder;

    /**
     * @var Invoker
     */
    private $invoker;

    /**
     * @var array<string,callable>
     */
    private $customMethods;
}
