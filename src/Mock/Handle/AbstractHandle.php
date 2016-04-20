<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2016 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Mock\Handle;

use Eloquent\Phony\Assertion\Recorder\AssertionRecorder;
use Eloquent\Phony\Assertion\Recorder\AssertionRecorderInterface;
use Eloquent\Phony\Assertion\Renderer\AssertionRenderer;
use Eloquent\Phony\Assertion\Renderer\AssertionRendererInterface;
use Eloquent\Phony\Event\EventCollectionInterface;
use Eloquent\Phony\Invocation\Invoker;
use Eloquent\Phony\Invocation\InvokerInterface;
use Eloquent\Phony\Mock\Exception\FinalMethodStubException;
use Eloquent\Phony\Mock\Exception\UndefinedMethodStubException;
use Eloquent\Phony\Mock\Method\WrappedCustomMethod;
use Eloquent\Phony\Mock\Method\WrappedMagicMethod;
use Eloquent\Phony\Mock\Method\WrappedMethod;
use Eloquent\Phony\Mock\Method\WrappedTraitMethod;
use Eloquent\Phony\Mock\Method\WrappedUncallableMethod;
use Eloquent\Phony\Mock\MockInterface;
use Eloquent\Phony\Spy\SpyInterface;
use Eloquent\Phony\Stub\Factory\StubFactory;
use Eloquent\Phony\Stub\Factory\StubFactoryInterface;
use Eloquent\Phony\Stub\Factory\StubVerifierFactory;
use Eloquent\Phony\Stub\Factory\StubVerifierFactoryInterface;
use Eloquent\Phony\Stub\StubVerifierInterface;
use ReflectionClass;
use ReflectionMethod;
use stdClass;

/**
 * An abstract base class for implementing handles.
 */
abstract class AbstractHandle implements HandleInterface
{
    /**
     * Construct a new handle.
     *
     * @param ReflectionClass                   $class               The class.
     * @param stdClass                          $state               The state.
     * @param ReflectionMethod|null             $callParentMethod    The call parent method, or null if no parent class exists.
     * @param ReflectionMethod|null             $callTraitMethod     The call trait method, or null if no trait methods are implemented.
     * @param ReflectionMethod|null             $callMagicMethod     The call magic method, or null if magic calls are not supported.
     * @param MockInterface|null                $mock                The mock, or null if this is a static handle.
     * @param StubFactoryInterface|null         $stubFactory         The stub factory to use.
     * @param StubVerifierFactoryInterface|null $stubVerifierFactory The stub verifier factory to use.
     * @param AssertionRendererInterface|null   $assertionRenderer   The assertion renderer to use.
     * @param AssertionRecorderInterface|null   $assertionRecorder   The assertion recorder to use.
     * @param InvokerInterface|null             $invoker             The invoker to use.
     */
    public function __construct(
        ReflectionClass $class,
        stdClass $state,
        ReflectionMethod $callParentMethod = null,
        ReflectionMethod $callTraitMethod = null,
        ReflectionMethod $callMagicMethod = null,
        MockInterface $mock = null,
        StubFactoryInterface $stubFactory,
        StubVerifierFactoryInterface $stubVerifierFactory,
        AssertionRendererInterface $assertionRenderer,
        AssertionRecorderInterface $assertionRecorder,
        InvokerInterface $invoker
    ) {
        $this->mock = $mock;
        $this->class = $class;
        $this->state = $state;
        $this->callParentMethod = $callParentMethod;
        $this->callTraitMethod = $callTraitMethod;
        $this->callMagicMethod = $callMagicMethod;
        $this->stubFactory = $stubFactory;
        $this->stubVerifierFactory = $stubVerifierFactory;
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

    /**
     * Get the class.
     *
     * @return ReflectionClass The class.
     */
    public function clazz()
    {
        return $this->class;
    }

    /**
     * Get the class name.
     *
     * @return string The class name.
     */
    public function className()
    {
        return $this->class->getName();
    }

    /**
     * Turn the mock into a full mock.
     *
     * @return $this This handle.
     */
    public function full()
    {
        $this->state->defaultAnswerCallback =
            'Eloquent\Phony\Stub\Stub::returnsEmptyAnswerCallback';

        return $this;
    }

    /**
     * Turn the mock into a partial mock.
     *
     * @return $this This handle.
     */
    public function partial()
    {
        $this->state->defaultAnswerCallback =
            'Eloquent\Phony\Stub\Stub::forwardsAnswerCallback';

        return $this;
    }

    /**
     * Set the callback to use when creating a default answer.
     *
     * @api
     *
     * @param callable $defaultAnswerCallback The default answer callback.
     *
     * @return $this This handle.
     */
    public function setDefaultAnswerCallback($defaultAnswerCallback)
    {
        $this->state->defaultAnswerCallback = $defaultAnswerCallback;

        return $this;
    }

    /**
     * Get the default answer callback.
     *
     * @api
     *
     * @return callable The default answer callback.
     */
    public function defaultAnswerCallback()
    {
        return $this->state->defaultAnswerCallback;
    }

    /**
     * Get the stubs.
     *
     * @return stdClass The stubs.
     */
    public function stubs()
    {
        return $this->state->stubs;
    }

    /**
     * Get a stub verifier.
     *
     * @param string  $name      The method name.
     * @param boolean $isNewRule True if a new rule should be started.
     *
     * @return StubVerifierInterface  The stub verifier.
     * @throws MockExceptionInterface If the stub does not exist.
     */
    public function stub($name, $isNewRule = true)
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
     * @return StubVerifierInterface  The stub verifier.
     * @throws MockExceptionInterface If the stub does not exist.
     */
    public function __get($name)
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
     * @return SpyInterface           The stub.
     * @throws MockExceptionInterface If the spy does not exist.
     */
    public function spy($name)
    {
        return $this->stub($name)->spy();
    }

    /**
     * Checks if there was no interaction with the mock.
     *
     * @return EventCollectionInterface|null The result.
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
     * Throws an exception unless there was no interaction with the mock.
     *
     * @return EventCollectionInterface The result.
     * @throws Exception                If the assertion fails, and the assertion recorder throws exceptions.
     */
    public function noInteraction()
    {
        if ($result = $this->checkNoInteraction()) {
            return $result;
        }

        $calls = array();

        foreach (get_object_vars($this->state->stubs) as $stub) {
            $calls = array_merge($calls, $stub->allCalls());
        }

        return $this->assertionRecorder->createFailure(
            sprintf(
                "Expected no interaction with %s. Calls:\n%s",
                $this->assertionRenderer->renderMock($this),
                $this->assertionRenderer->renderCalls($calls)
            )
        );
    }

    /**
     * Stop recording calls.
     *
     * @return $this This handle.
     */
    public function stopRecording()
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
    public function startRecording()
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
    public function state()
    {
        return $this->state;
    }

    /**
     * Create a new stub verifier.
     *
     * @param string $name The method name.
     *
     * @return StubVerifierInterface  The stub verifier.
     * @throws MockExceptionInterface If the method does not exist.
     */
    protected function createStub($name)
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

            $stub = $this->stubFactory->create(
                new WrappedMagicMethod(
                    $name,
                    $this->callMagicMethod,
                    isset($this->uncallableMethods[$magicKey]),
                    $this
                ),
                $mock,
                $this->state->defaultAnswerCallback
            );
        } elseif (isset($this->uncallableMethods[$key])) {
            $stub = $this->stubFactory->create(
                new WrappedUncallableMethod(
                    $this->class->getMethod($name),
                    $this
                ),
                $mock,
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
                $mock,
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
                $mock,
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
                new WrappedMethod($this->callParentMethod, $method, $this),
                $mock,
                $this->state->defaultAnswerCallback
            );
        }

        $stubVerifier = $this->stubVerifierFactory->create($stub);

        if (!$this->state->isRecording) {
            $stubVerifier->stopRecording();
        }

        return $stubVerifier;
    }

    protected $state;
    protected $class;
    protected $mock;
    private $uncallableMethods;
    private $traitMethods;
    private $callParentMethod;
    private $callTraitMethod;
    private $callMagicMethod;
    private $stubFactory;
    private $stubVerifierFactory;
    private $assertionRenderer;
    private $assertionRecorder;
    private $invoker;
    private $customMethods;
}
