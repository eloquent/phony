<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2016 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Stub\Answer\Builder;

use Eloquent\Phony\Invocation\InvokerInterface;
use Eloquent\Phony\Stub\Answer\CallRequestInterface;

/**
 * A detail class for generator answer builders without support for return
 * values.
 */
abstract class GeneratorAnswerBuilderDetail
{
    /**
     * Get the answer.
     *
     * @param array<tuple<boolean,mixed,boolean,mixed,array<CallRequestInterface>>> &$iterations      The iteration details.
     * @param array<CallRequestInterface>                                           &$requests        The call requests
     * @param Exception|Error|null                                                  &$exception       The exception to throw.
     * @param mixed                                                                 &$returnValue     The return value.
     * @param integer|null                                                          &$returnsArgument The index of the argument to return.
     * @param boolean                                                               &$returnsSelf     True if the self value should be returned.
     * @param InvokerInterface                                                      $invoker          The invoker to use.
     *
     * @return callable The answer.
     */
    public static function answer(
        array &$iterations,
        array &$requests,
        &$exception,
        &$returnValue,
        &$returnsArgument,
        &$returnsSelf,
        InvokerInterface $invoker
    ) {
        // @codeCoverageIgnoreStart
        return function ($self, $arguments) use (
            &$iterations,
            &$requests,
            &$exception,
            $invoker
        ) {
            // @codeCoverageIgnoreEnd
            foreach ($iterations as $iteration) {
                list($hasKey, $key, $hasValue, $value, $iterationRequests) =
                    $iteration;

                foreach ($iterationRequests as $request) {
                    $invoker->callWith(
                        $request->callback(),
                        $request->finalArguments($self, $arguments)
                    );
                }

                if ($hasKey) {
                    yield $key => $value;
                } elseif ($hasValue) {
                    yield $value;
                } else {
                    yield;
                }
            }

            foreach ($requests as $request) {
                $invoker->callWith(
                    $request->callback(),
                    $request->finalArguments($self, $arguments)
                );
            }

            if ($exception) {
                throw $exception;
            }
        };
    }

    private $invoker;
    private $requests;
    private $iterations;
}
