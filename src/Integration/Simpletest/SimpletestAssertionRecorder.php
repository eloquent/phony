<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2015 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Integration\Simpletest;

use Eloquent\Phony\Assertion\Exception\AssertionException;
use Eloquent\Phony\Assertion\Recorder\AssertionRecorder;
use Eloquent\Phony\Assertion\Recorder\AssertionRecorderInterface;
use Eloquent\Phony\Event\EventCollectionInterface;
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
     * Construct a new SimpleTest assertion recorder.
     *
     * @param SimpleTestContext|null $simpletestContext The SimpleTest context to use.
     */
    public function __construct(SimpleTestContext $simpletestContext = null)
    {
        if (null === $simpletestContext) {
            $simpletestContext = SimpleTest::getContext();
        }

        $this->simpletestContext = $simpletestContext;
    }

    /**
     * Get the SimpleTest context.
     *
     * @return SimpleTestContext The context.
     */
    public function simpletestContext()
    {
        return $this->simpletestContext;
    }

    /**
     * Record that a successful assertion occurred.
     *
     * @param array<EventInterface>|null $events The events.
     *
     * @return EventCollectionInterface The result.
     */
    public function createSuccess(array $events = null)
    {
        $this->simpletestContext->getReporter()->paintPass('');

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
        $flags = 0;

        if (defined('DEBUG_BACKTRACE_IGNORE_ARGS')) {
            $flags = DEBUG_BACKTRACE_IGNORE_ARGS;
        }

        $call = AssertionException::tracePhonyCall(\debug_backtrace($flags));

        if ($call && isset($call['file']) && isset($call['line'])) { // @codeCoverageIgnoreStart
            $description .= "\nat [$call[file] line $call[line]]";
        } // @codeCoverageIgnoreEnd

        $this->simpletestContext->getReporter()
            ->paintFail(preg_replace('/(\R)/', '$1   ', $description));
    }

    private static $instance;
    private $simpletestContext;
}
