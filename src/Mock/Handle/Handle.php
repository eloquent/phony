<?php

declare(strict_types=1);

namespace Eloquent\Phony\Mock\Handle;

use Eloquent\Phony\Event\EventCollection;
use Eloquent\Phony\Mock\Exception\MockException;
use Eloquent\Phony\Spy\Spy;
use Eloquent\Phony\Stub\StubVerifier;
use ReflectionClass;
use stdClass;
use Throwable;

/**
 * The interface implemented by handles.
 */
interface Handle
{
    /**
     * Get the class.
     *
     * @return ReflectionClass<object> The class.
     */
    public function class(): ReflectionClass;

    /**
     * Get the class name.
     *
     * @return string The class name.
     */
    public function className(): string;

    /**
     * Turn the mock into a full mock.
     *
     * @return $this This handle.
     */
    public function full(): self;

    /**
     * Turn the mock into a partial mock.
     *
     * @return $this This handle.
     */
    public function partial(): self;

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
    public function proxy($object): self;

    /**
     * Set the callback to use when creating a default answer.
     *
     * @param callable $defaultAnswerCallback The default answer callback.
     *
     * @return $this This handle.
     */
    public function setDefaultAnswerCallback(
        callable $defaultAnswerCallback
    ): self;

    /**
     * Get the default answer callback.
     *
     * @return callable The default answer callback.
     */
    public function defaultAnswerCallback(): callable;

    /**
     * Get a stub verifier.
     *
     * @param string $name      The method name.
     * @param bool   $isNewRule True if a new rule should be started.
     *
     * @return StubVerifier  The stub verifier.
     * @throws MockException If the stub does not exist.
     */
    public function stub(string $name, bool $isNewRule = true): StubVerifier;

    /**
     * Get a stub verifier.
     *
     * Using this method will always start a new rule.
     *
     * @param string $name The method name.
     *
     * @return StubVerifier  The stub verifier.
     * @throws MockException If the stub does not exist.
     */
    public function __get(string $name): StubVerifier;

    /**
     * Checks if there was no interaction with the mock.
     *
     * @return ?EventCollection The result.
     */
    public function checkNoInteraction(): ?EventCollection;

    /**
     * Record an assertion failure unless there was no interaction with the mock.
     *
     * @return ?EventCollection The result, or null if the assertion recorder does not throw exceptions.
     * @throws Throwable        If the assertion fails, and the assertion recorder throws exceptions.
     */
    public function noInteraction(): ?EventCollection;

    /**
     * Stop recording calls.
     *
     * @return $this This handle.
     */
    public function stopRecording(): self;

    /**
     * Start recording calls.
     *
     * @return $this This handle.
     */
    public function startRecording(): self;

    /**
     * Get a spy.
     *
     * @param string $name The method name.
     *
     * @return Spy           The spy.
     * @throws MockException If the spy does not exist.
     */
    public function spy(string $name): Spy;

    /**
     * Get the handle state.
     *
     * @return stdClass The state.
     */
    public function state(): stdClass;
}
