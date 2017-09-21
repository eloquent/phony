<?php

declare(strict_types=1);

namespace Eloquent\Phony\Mock\Handle;

use Eloquent\Phony\Event\EventCollection;
use Eloquent\Phony\Mock\Exception\FinalMethodStubException;
use Eloquent\Phony\Mock\Exception\UndefinedMethodStubException;
use Eloquent\Phony\Mock\Method\WrappedCustomMethod;
use Eloquent\Phony\Mock\Method\WrappedMagicMethod;
use Eloquent\Phony\Mock\Method\WrappedParentMethod;
use Eloquent\Phony\Mock\Method\WrappedTraitMethod;
use Eloquent\Phony\Mock\Method\WrappedUncallableMethod;
use Eloquent\Phony\Mock\Mock;
use Eloquent\Phony\Spy\Spy;
use Eloquent\Phony\Stub\StubData;
use Eloquent\Phony\Stub\StubVerifier;
use ReflectionClass;
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
     * @return ReflectionClass The class.
     */
    public function clazz(): ReflectionClass
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
     * @return EventCollection|null The result.
     */
    public function checkNoInteraction()
    {
        foreach (get_object_vars($this->state->stubs) as $stub) {
            if ($stub->checkCalled()) {
                return;
            }
        }

        return $this->assertionRecorder->createSuccess();
    }

    /**
     * Record an assertion failure unless there was no interaction with the mock.
     *
     * @return EventCollection|null The result, or null if the assertion recorder does not throw exceptions.
     * @throws Throwable            If the assertion fails, and the assertion recorder throws exceptions.
     */
    public function noInteraction()
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

    private function createStub($name)
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
                $returnValue = $this->emptyValueFactory->fromFunction(
                    $this->class->getMethod($magicKey)
                );
            } else {
                $isUncallable = false;
                $returnValue = null;
            }

            $stub = $this->stubFactory->create(
                new WrappedMagicMethod(
                    $name,
                    $this->callMagicMethod,
                    $isUncallable,
                    $this,
                    $returnValue
                ),
                $this->state->defaultAnswerCallback
            );
        } elseif (isset($this->uncallableMethods[$key])) {
            $method = $this->class->getMethod($name);
            $stub = $this->stubFactory->create(
                new WrappedUncallableMethod(
                    $method,
                    $this,
                    $this->emptyValueFactory->fromFunction($method)
                ),
                $this->state->defaultAnswerCallback
            );
        } elseif (isset($this->traitMethods[$key])) {
            $stub = $this->stubFactory->create(
                new WrappedTraitMethod(
                    $this->callTraitMethod,
                    $this->traitMethods[$key],
                    $this->class->getMethod($name),
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

            $stub = $this->stubFactory->create(
                new WrappedParentMethod($this->callParentMethod, $method, $this),
                $this->state->defaultAnswerCallback
            );
        }

        $stubVerifier = $this->stubVerifierFactory->create($stub, $mock);

        if (!$this->state->isRecording) {
            $stubVerifier->stopRecording();
        }

        return $stubVerifier;
    }

    private function constructHandle(
        $class,
        $state,
        $callParentMethod,
        $callTraitMethod,
        $callMagicMethod,
        $mock,
        $stubFactory,
        $stubVerifierFactory,
        $emptyValueFactory,
        $assertionRenderer,
        $assertionRecorder,
        $invoker
    ) {
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
        $this->uncallableMethods = $uncallableMethodsProperty->getValue(null);

        $traitMethodsProperty = $class->getProperty('_traitMethods');
        $traitMethodsProperty->setAccessible(true);
        $this->traitMethods = $traitMethodsProperty->getValue(null);

        $customMethodsProperty = $class->getProperty('_customMethods');
        $customMethodsProperty->setAccessible(true);
        $this->customMethods = $customMethodsProperty->getValue(null);
    }

    private $state;
    private $class;
    private $mock;
    private $uncallableMethods;
    private $traitMethods;
    private $callParentMethod;
    private $callTraitMethod;
    private $callMagicMethod;
    private $stubFactory;
    private $stubVerifierFactory;
    private $emptyValueFactory;
    private $assertionRenderer;
    private $assertionRecorder;
    private $invoker;
    private $customMethods;
}
