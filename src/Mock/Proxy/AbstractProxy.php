<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2015 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Mock\Proxy;

use Eloquent\Phony\Assertion\Recorder\AssertionRecorder;
use Eloquent\Phony\Assertion\Recorder\AssertionRecorderInterface;
use Eloquent\Phony\Assertion\Renderer\AssertionRenderer;
use Eloquent\Phony\Assertion\Renderer\AssertionRendererInterface;
use Eloquent\Phony\Event\EventCollectionInterface;
use Eloquent\Phony\Invocation\Invoker;
use Eloquent\Phony\Invocation\InvokerInterface;
use Eloquent\Phony\Matcher\WildcardMatcher;
use Eloquent\Phony\Matcher\WildcardMatcherInterface;
use Eloquent\Phony\Mock\Exception\UndefinedMethodStubException;
use Eloquent\Phony\Mock\Method\WrappedCustomMethod;
use Eloquent\Phony\Mock\Method\WrappedMagicMethod;
use Eloquent\Phony\Mock\Method\WrappedMethod;
use Eloquent\Phony\Mock\Method\WrappedTraitMethod;
use Eloquent\Phony\Mock\Method\WrappedUncallableMethod;
use Eloquent\Phony\Mock\MockInterface;
use Eloquent\Phony\Mock\Proxy\Exception\UndefinedPropertyException;
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
 * An abstract base class for implementing proxies.
 *
 * @internal
 */
abstract class AbstractProxy implements ProxyInterface
{
    /**
     * Construct a new proxy.
     *
     * @param ReflectionClass                   $class               The class.
     * @param stdClass|null                     $state               The state.
     * @param ReflectionMethod|null             $callParentMethod    The call parent method, or null if no parent class exists.
     * @param ReflectionMethod|null             $callTraitMethod     The call trait method, or null if no trait methods are implemented.
     * @param ReflectionMethod|null             $callMagicMethod     The call magic method, or null if magic calls are not supported.
     * @param MockInterface|null                $mock                The mock, or null if this is a static proxy.
     * @param StubFactoryInterface|null         $stubFactory         The stub factory to use.
     * @param StubVerifierFactoryInterface|null $stubVerifierFactory The stub verifier factory to use.
     * @param AssertionRendererInterface|null   $assertionRenderer   The assertion renderer to use.
     * @param AssertionRecorderInterface|null   $assertionRecorder   The assertion recorder to use.
     * @param WildcardMatcherInterface|null     $wildcardMatcher     The wildcard matcher to use.
     * @param InvokerInterface|null             $wildcardMatcher     The invoker to use.
     */
    public function __construct(
        ReflectionClass $class,
        stdClass $state = null,
        ReflectionMethod $callParentMethod = null,
        ReflectionMethod $callTraitMethod = null,
        ReflectionMethod $callMagicMethod = null,
        MockInterface $mock = null,
        StubFactoryInterface $stubFactory = null,
        StubVerifierFactoryInterface $stubVerifierFactory = null,
        AssertionRendererInterface $assertionRenderer = null,
        AssertionRecorderInterface $assertionRecorder = null,
        WildcardMatcherInterface $wildcardMatcher = null,
        InvokerInterface $invoker = null
    ) {
        if (null === $state) {
            $state = (object) array(
                'stubs' => (object) array(),
                'isFull' => false,
            );
        }
        if (null === $stubFactory) {
            $stubFactory = StubFactory::instance();
        }
        if (null === $stubVerifierFactory) {
            $stubVerifierFactory = StubVerifierFactory::instance();
        }
        if (null === $assertionRenderer) {
            $assertionRenderer = AssertionRenderer::instance();
        }
        if (null === $assertionRecorder) {
            $assertionRecorder = AssertionRecorder::instance();
        }
        if (null === $wildcardMatcher) {
            $wildcardMatcher = WildcardMatcher::instance();
        }
        if (null === $invoker) {
            $invoker = Invoker::instance();
        }

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
        $this->wildcardMatcher = $wildcardMatcher;
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
     * Get the stub factory.
     *
     * @return StubFactoryInterface The stub factory.
     */
    public function stubFactory()
    {
        return $this->stubFactory;
    }

    /**
     * Get the stub verifier factory.
     *
     * @return StubVerifierFactoryInterface The stub verifier factory.
     */
    public function stubVerifierFactory()
    {
        return $this->stubVerifierFactory;
    }

    /**
     * Get the assertion renderer.
     *
     * @return AssertionRendererInterface The assertion renderer.
     */
    public function assertionRenderer()
    {
        return $this->assertionRenderer;
    }

    /**
     * Get the assertion recorder.
     *
     * @return AssertionRecorderInterface The assertion recorder.
     */
    public function assertionRecorder()
    {
        return $this->assertionRecorder;
    }

    /**
     * Get the wildcard matcher.
     *
     * @return WildcardMatcherInterface The wildcard matcher.
     */
    public function wildcardMatcher()
    {
        return $this->wildcardMatcher;
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
     * @return ProxyInterface This proxy.
     */
    public function full()
    {
        $this->state->isFull = true;

        return $this;
    }

    /**
     * Turn the mock into a partial mock.
     *
     * @return ProxyInterface This proxy.
     */
    public function partial()
    {
        $this->state->isFull = false;

        return $this;
    }

    /**
     * Returns true if the mock is a full mock.
     *
     * @return boolean True if the mock is a full mock.
     */
    public function isFull()
    {
        return $this->state->isFull;
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
     * @param string $name The method name.
     *
     * @return StubVerifierInterface  The stub verifier.
     * @throws MockExceptionInterface If the stub does not exist.
     */
    public function stub($name)
    {
        $key = strtolower($name);

        if (!isset($this->state->stubs->$key)) {
            $this->state->stubs->$key = $this->createStub($name);
        }

        return $this->state->stubs->$key;
    }

    /**
     * Get a stub verifier.
     *
     * @param string $name The method name.
     *
     * @return StubVerifierInterface  The stub verifier.
     * @throws MockExceptionInterface If the stub does not exist.
     */
    public function __get($name)
    {
        try {
            $stub = $this->stub($name);
        } catch (UndefinedMethodStubException $e) {
            throw new UndefinedPropertyException(get_called_class(), $name, $e);
        }

        return $stub->with($this->wildcardMatcher);
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
     * Reset the mock to its initial state.
     *
     * @return ProxyInterface This proxy.
     */
    public function reset()
    {
        foreach (get_object_vars($this->state->stubs) as $name => $stub) {
            unset($this->state->stubs->$name);
        }

        return $this;
    }

    /**
     * Get the proxy state.
     *
     * @internal
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
                $mock
            );
        } elseif (isset($this->uncallableMethods[$key])) {
            $stub = $this->stubFactory->create(
                new WrappedUncallableMethod(
                    $this->class->getMethod($name),
                    $this
                ),
                $mock
            );
        } elseif (isset($this->traitMethods[$key])) {
            $stub = $this->stubFactory->create(
                new WrappedTraitMethod(
                    $this->callTraitMethod,
                    $this->traitMethods[$key],
                    $this->class->getMethod($name),
                    $this
                ),
                $mock
            );
        } elseif (array_key_exists($key, $this->customMethods)) {
            $stub = $this->stubFactory->create(
                new WrappedCustomMethod(
                    $this->customMethods[$key],
                    $this->class->getMethod($name),
                    $this,
                    $this->invoker
                ),
                $mock
            );
        } else {
            $stub = $this->stubFactory->create(
                new WrappedMethod(
                    $this->callParentMethod,
                    $this->class->getMethod($name),
                    $this
                ),
                $mock
            );
        }

        if ($this->state->isFull) {
            $stub->returns()->with($this->wildcardMatcher);
        }

        return $this->stubVerifierFactory->create($stub);
    }

    protected $state;
    private $mock;
    private $class;
    private $uncallableMethods;
    private $traitMethods;
    private $callParentMethod;
    private $callTraitMethod;
    private $callMagicMethod;
    private $stubFactory;
    private $stubVerifierFactory;
    private $assertionRenderer;
    private $assertionRecorder;
    private $wildcardMatcher;
    private $invoker;
    private $customMethods;
}
