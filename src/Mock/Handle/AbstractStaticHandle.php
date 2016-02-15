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

use Eloquent\Phony\Assertion\Recorder\AssertionRecorderInterface;
use Eloquent\Phony\Assertion\Renderer\AssertionRendererInterface;
use Eloquent\Phony\Invocation\InvokerInterface;
use Eloquent\Phony\Stub\Factory\StubFactoryInterface;
use Eloquent\Phony\Stub\Factory\StubVerifierFactoryInterface;
use ReflectionClass;
use ReflectionObject;
use stdClass;

/**
 * An abstract base class for implementing static handles.
 */
abstract class AbstractStaticHandle extends AbstractHandle implements
    StaticHandleInterface
{
    /**
     * Construct a new static handle.
     *
     * @param ReflectionClass                   $class               The class.
     * @param stdClass|null                     $state               The state.
     * @param StubFactoryInterface|null         $stubFactory         The stub factory to use.
     * @param StubVerifierFactoryInterface|null $stubVerifierFactory The stub verifier factory to use.
     * @param AssertionRendererInterface|null   $assertionRenderer   The assertion renderer to use.
     * @param AssertionRecorderInterface|null   $assertionRecorder   The assertion recorder to use.
     * @param InvokerInterface|null             $invoker             The invoker to use.
     */
    public function __construct(
        ReflectionClass $class,
        stdClass $state = null,
        StubFactoryInterface $stubFactory = null,
        StubVerifierFactoryInterface $stubVerifierFactory = null,
        AssertionRendererInterface $assertionRenderer = null,
        AssertionRecorderInterface $assertionRecorder = null,
        InvokerInterface $invoker = null
    ) {
        if (!$state) {
            $state = (object) array(
                'defaultAnswerCallback' =>
                    'Eloquent\Phony\Stub\Stub::forwardsAnswerCallback',
                'stubs' => (object) array(),
                'isRecording' => true,
            );
        }
        if ($class->hasMethod('_callParentStatic')) {
            $callParentMethod = $class->getMethod('_callParentStatic');
            $callParentMethod->setAccessible(true);
        } else {
            $callParentMethod = null;
        }

        if ($class->hasMethod('_callTraitStatic')) {
            $callTraitMethod = $class->getMethod('_callTraitStatic');
            $callTraitMethod->setAccessible(true);
        } else {
            $callTraitMethod = null;
        }

        if ($class->hasMethod('_callMagicStatic')) {
            $callMagicMethod = $class->getMethod('_callMagicStatic');
            $callMagicMethod->setAccessible(true);
        } else {
            $callMagicMethod = null;
        }

        parent::__construct(
            $class,
            $state,
            $callParentMethod,
            $callTraitMethod,
            $callMagicMethod,
            null,
            $stubFactory,
            $stubVerifierFactory,
            $assertionRenderer,
            $assertionRecorder,
            $invoker
        );
    }

    /**
     * Use the supplied object as the implementation for all methods of the
     * mock.
     *
     * This method may help when partial mocking of a particular implementation
     * is not possible; as in the case of a final class.
     *
     * @param object $object The object to use.
     *
     * @return $this This handle.
     */
    public function proxy($object)
    {
        $reflector = new ReflectionObject($object);

        foreach ($reflector->getMethods() as $method) {
            if (!$method->isStatic() || $method->isPrivate()) {
                continue;
            }

            $name = $method->getName();

            if ($this->class->hasMethod($name)) {
                $method->setAccessible(true);

                $this->stub($name)->doesWith(
                    function ($arguments) use ($method, $object) {
                        return $method->invokeArgs($object, $arguments->all());
                    },
                    array(),
                    false,
                    true,
                    false
                );
            }
        }

        return $this;
    }
}
