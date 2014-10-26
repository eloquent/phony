<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Mock\Proxy;

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
     * @param stdClass|null                     $stubs               The stubs.
     * @param boolean|null                      $isFull              True if the mock is a full mock.
     * @param StubFactoryInterface|null         $stubFactory         The stub factory to use.
     * @param StubVerifierFactoryInterface|null $stubVerifierFactory The stub verifier factory to use.
     * @param WildcardMatcherInterface|null     $wildcardMatcher     The wildcard matcher to use.
     */
    public function __construct(
        ReflectionClass $class,
        stdClass $stubs = null,
        $isFull = null,
        StubFactoryInterface $stubFactory = null,
        StubVerifierFactoryInterface $stubVerifierFactory = null,
        WildcardMatcherInterface $wildcardMatcher = null
    ) {
        if ($class->hasMethod('_callParentStatic')) {
            $callParentMethod = $class->getMethod('_callParentStatic');
            $callParentMethod->setAccessible(true);
        } else {
            $callParentMethod = null;
        }

        if ($class->hasMethod('_callMagicStatic')) {
            $callMagicMethod = $class->getMethod('_callMagicStatic');
            $callMagicMethod->setAccessible(true);
        } else {
            $callMagicMethod = null;
        }

        parent::__construct(
            $class,
            $stubs,
            $isFull,
            $callParentMethod,
            $callMagicMethod,
            null,
            $stubFactory,
            $stubVerifierFactory,
            $wildcardMatcher
        );
    }
}
