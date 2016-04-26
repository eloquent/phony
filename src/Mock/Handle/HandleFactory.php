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

use Eloquent\Phony\Assertion\AssertionRecorder;
use Eloquent\Phony\Assertion\AssertionRenderer;
use Eloquent\Phony\Assertion\ExceptionAssertionRecorder;
use Eloquent\Phony\Invocation\Invoker;
use Eloquent\Phony\Mock\Exception\InvalidMockClassException;
use Eloquent\Phony\Mock\Exception\InvalidMockException;
use Eloquent\Phony\Mock\Exception\MockException;
use Eloquent\Phony\Mock\Exception\NonMockClassException;
use Eloquent\Phony\Mock\Handle\Stubbing\InstanceStubbingHandle;
use Eloquent\Phony\Mock\Handle\Stubbing\StaticStubbingHandle;
use Eloquent\Phony\Mock\Handle\Verification\InstanceVerificationHandle;
use Eloquent\Phony\Mock\Handle\Verification\StaticVerificationHandle;
use Eloquent\Phony\Mock\Mock;
use Eloquent\Phony\Stub\StubFactory;
use Eloquent\Phony\Stub\StubVerifierFactory;
use ReflectionClass;
use ReflectionException;

/**
 * Creates handles.
 */
class HandleFactory
{
    /**
     * Get the static instance of this factory.
     *
     * @return HandleFactory The static factory.
     */
    public static function instance()
    {
        if (!self::$instance) {
            self::$instance = new self(
                StubFactory::instance(),
                StubVerifierFactory::instance(),
                AssertionRenderer::instance(),
                ExceptionAssertionRecorder::instance(),
                Invoker::instance()
            );
        }

        return self::$instance;
    }

    /**
     * Construct a new handle factory.
     *
     * @param StubFactory         $stubFactory         The stub factory to use.
     * @param StubVerifierFactory $stubVerifierFactory The stub verifier factory to use.
     * @param AssertionRenderer   $assertionRenderer   The assertion renderer to use.
     * @param AssertionRecorder   $assertionRecorder   The assertion recorder to use.
     * @param Invoker             $invoker             The invoker to use.
     */
    public function __construct(
        StubFactory $stubFactory,
        StubVerifierFactory $stubVerifierFactory,
        AssertionRenderer $assertionRenderer,
        AssertionRecorder $assertionRecorder,
        Invoker $invoker
    ) {
        $this->stubFactory = $stubFactory;
        $this->stubVerifierFactory = $stubVerifierFactory;
        $this->assertionRenderer = $assertionRenderer;
        $this->assertionRecorder = $assertionRecorder;
        $this->invoker = $invoker;
    }

    /**
     * Create a new stubbing handle.
     *
     * @param Mock|InstanceHandle $mock  The mock.
     * @param string|null         $label The label.
     *
     * @return InstanceStubbingHandle The newly created handle.
     * @throws MockException          If the supplied mock is invalid.
     */
    public function createStubbing($mock, $label = null)
    {
        if ($mock instanceof InstanceStubbingHandle) {
            return $mock;
        }

        if ($mock instanceof InstanceHandle) {
            $mock = $mock->mock();
        }

        if (!$mock instanceof Mock) {
            throw new InvalidMockException($mock);
        }

        $class = new ReflectionClass($mock);

        $handleProperty = $class->getProperty('_handle');
        $handleProperty->setAccessible(true);

        if ($handle = $handleProperty->getValue($mock)) {
            return $handle;
        }

        $handle = new InstanceStubbingHandle(
            $mock,
            (object) array(
                'defaultAnswerCallback' =>
                    'Eloquent\Phony\Stub\StubData::returnsEmptyAnswerCallback',
                'stubs' => (object) array(),
                'isRecording' => true,
                'label' => $label,
            ),
            $this->stubFactory,
            $this->stubVerifierFactory,
            $this->assertionRenderer,
            $this->assertionRecorder,
            $this->invoker
        );

        $handleProperty->setValue($mock, $handle);

        return $handle;
    }

    /**
     * Create a new verification handle.
     *
     * @param Mock|InstanceHandle $mock The mock.
     *
     * @return InstanceVerificationHandle The newly created handle.
     * @throws MockException              If the supplied mock is invalid.
     */
    public function createVerification($mock)
    {
        if ($mock instanceof InstanceVerificationHandle) {
            return $mock;
        }

        $stubbingHandle = $this->createStubbing($mock);

        return new InstanceVerificationHandle(
            $stubbingHandle->mock(),
            $stubbingHandle->state(),
            $this->stubFactory,
            $this->stubVerifierFactory,
            $this->assertionRenderer,
            $this->assertionRecorder,
            $this->invoker
        );
    }

    /**
     * Create a new static stubbing handle.
     *
     * @param Mock|Handle|ReflectionClass|string $class The class.
     *
     * @return StaticStubbingHandle The newly created handle.
     * @throws MockException        If the supplied class name is not a mock class.
     */
    public function createStubbingStatic($class)
    {
        if ($class instanceof StaticStubbingHandle) {
            return $class;
        }

        if ($class instanceof Handle) {
            $class = $class->clazz();
        } elseif ($class instanceof Mock) {
            $class = new ReflectionClass($class);
        } elseif (is_string($class)) {
            try {
                $class = new ReflectionClass($class);
            } catch (ReflectionException $e) {
                throw new NonMockClassException($class, $e);
            }
        } elseif (!$class instanceof ReflectionClass) {
            throw new InvalidMockClassException($class);
        }

        if (!$class->isSubclassOf('Eloquent\Phony\Mock\Mock')) {
            throw new NonMockClassException($class->getName());
        }

        $handleProperty = $class->getProperty('_staticHandle');
        $handleProperty->setAccessible(true);

        if ($handle = $handleProperty->getValue(null)) {
            return $handle;
        }

        $handle = new StaticStubbingHandle(
            $class,
            (object) array(
                'defaultAnswerCallback' =>
                    'Eloquent\Phony\Stub\StubData::forwardsAnswerCallback',
                'stubs' => (object) array(),
                'isRecording' => true,
            ),
            $this->stubFactory,
            $this->stubVerifierFactory,
            $this->assertionRenderer,
            $this->assertionRecorder,
            $this->invoker
        );

        $handleProperty->setValue(null, $handle);

        return $handle;
    }

    /**
     * Create a new static verification handle.
     *
     * @param Mock|Handle|ReflectionClass|string $class The class.
     *
     * @return StaticVerificationHandle The newly created handle.
     * @throws MockException            If the supplied class name is not a mock class.
     */
    public function createVerificationStatic($class)
    {
        if ($class instanceof StaticVerificationHandle) {
            return $class;
        }

        $stubbingHandle = $this->createStubbingStatic($class);

        return new StaticVerificationHandle(
            $stubbingHandle->clazz(),
            $stubbingHandle->state(),
            $this->stubFactory,
            $this->stubVerifierFactory,
            $this->assertionRenderer,
            $this->assertionRecorder,
            $this->invoker
        );
    }

    private static $instance;
    private $mockFactory;
    private $stubVerifierFactory;
    private $assertionRenderer;
    private $assertionRecorder;
    private $invoker;
}
