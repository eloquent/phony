<?php

declare(strict_types=1);

namespace Eloquent\Phony\Invocation;

use Eloquent\Phony\Call\Arguments;

/**
 * Used for implementing wrapped invocables.
 */
trait WrappedInvocableTrait
{
    /**
     * Returns true if anonymous.
     *
     * @return bool True if anonymous.
     */
    public function isAnonymous(): bool
    {
        return $this->isAnonymous;
    }

    /**
     * Get the callback.
     *
     * @return ?callable The callback.
     */
    public function callback(): ?callable
    {
        return $this->callback;
    }

    /**
     * Set the label.
     *
     * @param string $label The label.
     *
     * @return $this This invocable.
     */
    public function setLabel(string $label): WrappedInvocable
    {
        $this->label = $label;

        return $this;
    }

    /**
     * Get the label.
     *
     * @return string The label.
     */
    public function label(): string
    {
        return $this->label;
    }

    /**
     * Invoke this object.
     *
     * @param mixed ...$arguments The arguments.
     *
     * @return mixed     The result of invocation.
     * @throws Throwable If an error occurs.
     */
    public function invoke(...$arguments)
    {
        return $this->invokeWith($arguments);
    }

    /**
     * Invoke this object.
     *
     * @param mixed ...$arguments The arguments.
     *
     * @return mixed     The result of invocation.
     * @throws Throwable If an error occurs.
     */
    public function __invoke(...$arguments)
    {
        return $this->invokeWith($arguments);
    }

    /**
     * Invoke this object.
     *
     * This method supports reference parameters.
     *
     * @param Arguments|array<int,mixed> $arguments The arguments.
     *
     * @return mixed     The result of invocation.
     * @throws Throwable If an error occurs.
     */
    abstract public function invokeWith($arguments = []);

    /**
     * @var ?callable
     */
    protected $callback;

    /**
     * @var bool
     */
    protected $isAnonymous = false;

    /**
     * @var string
     */
    protected $label = '';
}
