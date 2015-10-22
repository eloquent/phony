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

use Eloquent\Phony\Assertion\Recorder\AssertionRecorderInterface;
use Eloquent\Phony\Assertion\Renderer\AssertionRendererInterface;
use Eloquent\Phony\Invocation\InvokerInterface;
use Eloquent\Phony\Matcher\WildcardMatcherInterface;
use Eloquent\Phony\Stub\Factory\StubFactoryInterface;
use Eloquent\Phony\Stub\Factory\StubVerifierFactoryInterface;
use ReflectionClass;
use stdClass;

/**
 * An abstract base class for implementing static proxies.
 *
 * @internal
 */
abstract class AbstractStaticProxy extends AbstractProxy implements
    StaticProxyInterface
{
    /**
     * Construct a new static proxy.
     *
     * @param ReflectionClass                   $class               The class.
     * @param stdClass|null                     $state               The state.
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
        StubFactoryInterface $stubFactory = null,
        StubVerifierFactoryInterface $stubVerifierFactory = null,
        AssertionRendererInterface $assertionRenderer = null,
        AssertionRecorderInterface $assertionRecorder = null,
        WildcardMatcherInterface $wildcardMatcher = null,
        InvokerInterface $invoker = null
    ) {
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
            $wildcardMatcher,
            $invoker
        );
    }
}
