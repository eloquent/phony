<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Mock\Exception;

use Eloquent\Phony\Mock\Builder\MockBuilderInterface;
use Exception;

/**
 * The requested method stub does not exist.
 *
 * @internal
 */
final class UndefinedMethodStubException extends Exception implements
    MockExceptionInterface
{
    /**
     * Construct a new undefined method stub exception.
     *
     * @param MockBuilderInterface $mockBuilder The mock builder.
     * @param string               $name        The method name.
     * @param Exception|null       $cause       The cause, if available.
     */
    public function __construct(
        MockBuilderInterface $mockBuilder,
        $name,
        Exception $cause = null
    ) {
        $this->mockBuilder = $mockBuilder;
        $this->name = $name;

        parent::__construct(
            sprintf(
                'The requested method stub %s::%s() does not exist.',
                $mockBuilder->className(),
                $name
            ),
            0,
            $cause
        );
    }

    /**
     * Get the mock builder.
     *
     * @return MockBuilderInterface The mock builder.
     */
    public function mockBuilder()
    {
        return $this->mockBuilder;
    }

    /**
     * Get the method name.
     *
     * @return string The method name.
     */
    public function name()
    {
        return $this->name;
    }

    private $mockBuilder;
    private $name;
}
