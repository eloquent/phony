<?php

declare(strict_types=1);

namespace Eloquent\Phony\Spy;

use Eloquent\Phony\Call\Call;
use Eloquent\Phony\Event\EventCollection;
use Eloquent\Phony\Invocation\WrappedInvocable;

/**
 * The interface implemented by spies.
 */
interface Spy extends WrappedInvocable, EventCollection
{
    /**
     * Turn on or off the use of generator spies.
     *
     * @param bool $useGeneratorSpies True to use generator spies.
     *
     * @return $this This spy.
     */
    public function setUseGeneratorSpies(bool $useGeneratorSpies): self;

    /**
     * Returns true if this spy uses generator spies.
     *
     * @return bool True if this spy uses generator spies.
     */
    public function useGeneratorSpies(): bool;

    /**
     * Turn on or off the use of iterable spies.
     *
     * @param bool $useIterableSpies True to use iterable spies.
     *
     * @return $this This spy.
     */
    public function setUseIterableSpies(bool $useIterableSpies): self;

    /**
     * Returns true if this spy uses iterable spies.
     *
     * @return bool True if this spy uses iterable spies.
     */
    public function useIterableSpies(): bool;

    /**
     * Stop recording calls.
     *
     * @return $this This spy.
     */
    public function stopRecording(): self;

    /**
     * Start recording calls.
     *
     * @return $this This spy.
     */
    public function startRecording(): self;

    /**
     * Set the calls.
     *
     * @param array<int,Call> $calls The calls.
     */
    public function setCalls(array $calls): void;

    /**
     * Add a call.
     *
     * @param Call $call The call.
     */
    public function addCall(Call $call): void;
}
