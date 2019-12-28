<?php

declare(strict_types=1);

namespace Eloquent\Phony\Mock\Handle;

use Eloquent\Phony\Assertion\AssertionRecorder;
use Eloquent\Phony\Assertion\AssertionRenderer;
use Eloquent\Phony\Assertion\ExceptionAssertionRecorder;
use Eloquent\Phony\Invocation\Invoker;
use Eloquent\Phony\Mock\Exception\InvalidMockClassException;
use Eloquent\Phony\Mock\Exception\InvalidMockException;
use Eloquent\Phony\Mock\Exception\MockException;
use Eloquent\Phony\Mock\Exception\NonMockClassException;
use Eloquent\Phony\Mock\Mock;
use Eloquent\Phony\Stub\EmptyValueFactory;
use Eloquent\Phony\Stub\StubData;
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
     * Get the static instance of this class.
     *
     * @return self The static instance.
     */
    public static function instance(): self
    {
        if (!self::$instance) {
            self::$instance = new self(
                StubFactory::instance(),
                StubVerifierFactory::instance(),
                EmptyValueFactory::instance(),
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
     * @param EmptyValueFactory   $emptyValueFactory   The empty value factory to use.
     * @param AssertionRenderer   $assertionRenderer   The assertion renderer to use.
     * @param AssertionRecorder   $assertionRecorder   The assertion recorder to use.
     * @param Invoker             $invoker             The invoker to use.
     */
    public function __construct(
        StubFactory $stubFactory,
        StubVerifierFactory $stubVerifierFactory,
        EmptyValueFactory $emptyValueFactory,
        AssertionRenderer $assertionRenderer,
        AssertionRecorder $assertionRecorder,
        Invoker $invoker
    ) {
        $this->stubFactory = $stubFactory;
        $this->stubVerifierFactory = $stubVerifierFactory;
        $this->emptyValueFactory = $emptyValueFactory;
        $this->assertionRenderer = $assertionRenderer;
        $this->assertionRecorder = $assertionRecorder;
        $this->invoker = $invoker;
    }

    /**
     * Create a new handle.
     *
     * @param Mock|InstanceHandle $mock  The mock.
     * @param string              $label The label.
     *
     * @return InstanceHandle The newly created handle.
     * @throws MockException  If the supplied mock is invalid.
     */
    public function instanceHandle($mock, string $label = ''): InstanceHandle
    {
        if ($mock instanceof InstanceHandle) {
            return $mock;
        }

        if (!$mock instanceof Mock) {
            throw new InvalidMockException($mock);
        }

        $class = new ReflectionClass($mock);

        $handleProperty = $class->getProperty('_handle');
        $handleProperty->setAccessible(true);

        $handle = @$handleProperty->getValue($mock);

        if ($handle) {
            return $handle;
        }

        $handle = new InstanceHandle(
            $mock,
            (object) [
                'defaultAnswerCallback' =>
                    [StubData::class, 'returnsEmptyAnswerCallback'],
                'stubs' => (object) [],
                'isRecording' => true,
                'label' => $label,
            ],
            $this->stubFactory,
            $this->stubVerifierFactory,
            $this->emptyValueFactory,
            $this->assertionRenderer,
            $this->assertionRecorder,
            $this->invoker
        );

        @$handleProperty->setValue($mock, $handle);

        return $handle;
    }

    /**
     * Create a new static handle.
     *
     * @param Mock|Handle|ReflectionClass<object>|string $class The class.
     *
     * @return StaticHandle  The newly created handle.
     * @throws MockException If the supplied class name is not a mock class.
     */
    public function staticHandle($class): StaticHandle
    {
        if ($class instanceof StaticHandle) {
            return $class;
        }

        if ($class instanceof Handle) {
            $class = $class->class();
        } elseif ($class instanceof Mock) {
            $class = new ReflectionClass($class);
        } elseif (is_string($class)) {
            /** @var class-string $classString */
            $classString = $class;

            try {
                $class = new ReflectionClass($classString);
            } catch (ReflectionException $e) {
                throw new NonMockClassException($classString, $e);
            }
        } elseif (!$class instanceof ReflectionClass) {
            throw new InvalidMockClassException($class);
        }

        if (!$class->isSubclassOf(Mock::class)) {
            throw new NonMockClassException($class->getName());
        }

        $handleProperty = $class->getProperty('_staticHandle');
        $handleProperty->setAccessible(true);

        if ($handle = $handleProperty->getValue()) {
            return $handle;
        }

        $handle = new StaticHandle(
            $class,
            (object) [
                'defaultAnswerCallback' =>
                    [StubData::class, 'forwardsAnswerCallback'],
                'stubs' => (object) [],
                'isRecording' => true,
            ],
            $this->stubFactory,
            $this->stubVerifierFactory,
            $this->emptyValueFactory,
            $this->assertionRenderer,
            $this->assertionRecorder,
            $this->invoker
        );

        $handleProperty->setValue(null, $handle);

        return $handle;
    }

    /**
     * @var ?self
     */
    private static $instance;

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
}
