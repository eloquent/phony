<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Call\Exception;

use Eloquent\Phony\Assertion\Exception\AssertionExceptionInterface;
use Eloquent\Phony\Call\CallInterface;
use Eloquent\Phony\Matcher\MatcherInterface;
use Exception;

/**
 * The call return value did not match.
 */
final class CallReturnAssertionException extends Exception implements
    AssertionExceptionInterface
{
    /**
     * Construct a new call return assertion exception.
     *
     * @param CallInterface    $call    The call.
     * @param MatcherInterface $matcher The matcher.
     * @param Exception|null   $cause   The cause, if available.
     */
    public function __construct(
        CallInterface $call,
        MatcherInterface $matcher,
        Exception $cause = null
    ) {
        $this->call = $call;
        $this->matcher = $matcher;

        parent::__construct(
            sprintf(
                'The return value did not match <%s>.',
                $matcher->describe()
            ),
            0,
            $cause
        );
    }

    /**
     * Get the call.
     *
     * @return CallInterface The call.
     */
    public function call()
    {
        return $this->call;
    }

    /**
     * Get the matcher.
     *
     * @return MatcherInterface The matcher.
     */
    public function matcher()
    {
        return $this->matcher;
    }

    private $call;
    private $matcher;
}
