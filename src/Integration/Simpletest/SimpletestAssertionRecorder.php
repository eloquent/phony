<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Integration\Simpletest;

use Eloquent\Phony\Assertion\Exception\AssertionException;
use Eloquent\Phony\Assertion\Recorder\AssertionRecorder;
use Eloquent\Phony\Assertion\Recorder\AssertionRecorderInterface;
use Eloquent\Phony\Call\Event\CallEventCollectionInterface;
use Eloquent\Phony\Event\EventInterface;
use Exception;
use SimpleTest;
use SimpleTestContext;

/**
 * An assertion recorder for SimpleTest.
 *
 * @internal
 */
class SimpletestAssertionRecorder extends AssertionRecorder
{
    /**
     * Get the static instance of this recorder.
     *
     * @return AssertionRecorderInterface The static recorder.
     */
    public static function instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Record that a successful assertion occurred.
     *
     * @param array<integer,EventInterface>|null $events The events.
     *
     * @return CallEventCollectionInterface The result.
     */
    public function createSuccess(array $events = null)
    {
        $this->context()->getReporter()->paintPass('');

        return parent::createSuccess($events);
    }

    /**
     * Create a new assertion failure exception.
     *
     * @param string $description The failure description.
     *
     * @throws Exception If this recorder throws exceptions.
     */
    public function createFailure($description)
    {
        $call = AssertionException
            ::tracePhonyCall(\debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS));

        if ($call) {
            $description .= "\nat [$call[file] line $call[line]]";
        }

        $this->context()->getReporter()
            ->paintFail(preg_replace('/(\R)/', '$1   ', $description));
    }

    /**
     * Get the SimpleTest context.
     *
     * @return SimpleTestContext The context.
     */
    protected function context()
    {
        return SimpleTest::getContext();
    }

    private static $instance;
    private $passesProperty;
}
