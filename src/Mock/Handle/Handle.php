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

use Eloquent\Phony\Event\EventCollection;
use Eloquent\Phony\Mock\Exception\MockException;
use Eloquent\Phony\Stub\StubVerifier;
use Exception;
use ReflectionClass;
use stdClass;

/**
 * The interface implemented by handles.
 *
 * @api
 */
interface Handle
{
    /**
     * Get the class.
     *
     * @api
     *
     * @return ReflectionClass The class.
     */
    public function clazz();

    /**
     * Get the class name.
     *
     * @api
     *
     * @return string The class name.
     */
    public function className();

    /**
     * Turn the mock into a full mock.
     *
     * @api
     *
     * @return $this This handle.
     */
    public function full();

    /**
     * Turn the mock into a partial mock.
     *
     * @api
     *
     * @return $this This handle.
     */
    public function partial();

    /**
     * Use the supplied object as the implementation for all methods of the
     * mock.
     *
     * This method may help when partial mocking of a particular implementation
     * is not possible; as in the case of a final class.
     *
     * @api
     *
     * @param object $object The object to use.
     *
     * @return $this This handle.
     */
    public function proxy($object);

    /**
     * Set the callback to use when creating a default answer.
     *
     * @api
     *
     * @param callable $defaultAnswerCallback The default answer callback.
     *
     * @return $this This handle.
     */
    public function setDefaultAnswerCallback($defaultAnswerCallback);

    /**
     * Get the default answer callback.
     *
     * @api
     *
     * @return callable The default answer callback.
     */
    public function defaultAnswerCallback();

    /**
     * Get a stub verifier.
     *
     * @api
     *
     * @param string  $name      The method name.
     * @param boolean $isNewRule True if a new rule should be started.
     *
     * @return StubVerifier  The stub verifier.
     * @throws MockException If the stub does not exist.
     */
    public function stub($name, $isNewRule = true);

    /**
     * Get a stub verifier.
     *
     * Using this method will always start a new rule.
     *
     * @api
     *
     * @param string $name The method name.
     *
     * @return StubVerifier  The stub verifier.
     * @throws MockException If the stub does not exist.
     */
    public function __get($name);

    /**
     * Checks if there was no interaction with the mock.
     *
     * @api
     *
     * @return EventCollection|null The result.
     */
    public function checkNoInteraction();

    /**
     * Throws an exception unless there was no interaction with the mock.
     *
     * @api
     *
     * @return EventCollection The result.
     * @throws Exception       If the assertion fails, and the assertion recorder throws exceptions.
     */
    public function noInteraction();

    /**
     * Stop recording calls.
     *
     * @api
     *
     * @return $this This handle.
     */
    public function stopRecording();

    /**
     * Start recording calls.
     *
     * @api
     *
     * @return $this This handle.
     */
    public function startRecording();

    /**
     * Get the stubs.
     *
     * @return stdClass The stubs.
     */
    public function stubs();

    /**
     * Get a spy.
     *
     * @param string $name The method name.
     *
     * @return Spy           The spy.
     * @throws MockException If the spy does not exist.
     */
    public function spy($name);

    /**
     * Get the handle state.
     *
     * @return stdClass The state.
     */
    public function state();
}
