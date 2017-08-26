<?php

declare(strict_types=1);

namespace Eloquent\Phony\Mock\Method;

use Eloquent\Phony\Call\Arguments;
use Eloquent\Phony\Invocation\WrappedInvocableTrait;
use Eloquent\Phony\Mock\Handle\Handle;
use ReflectionMethod;
use Throwable;

/**
 * A wrapper that allows calling of the parent magic method in mocks.
 */
class WrappedMagicMethod implements WrappedMethod
{
    use WrappedInvocableTrait;

    /**
     * Construct a new wrapped magic method.
     *
     * @param string           $name            The name.
     * @param ReflectionMethod $callMagicMethod The _callMagic() method.
     * @param bool             $isUncallable    True if the underlying magic method is uncallable.
     * @param Handle           $handle          The handle.
     * @param mixed            $returnValue     The return value.
     */
    public function __construct(
        string $name,
        ReflectionMethod $callMagicMethod,
        bool $isUncallable,
        Handle $handle,
        $returnValue
    ) {
        $this->name = $name;
        $this->callMagicMethod = $callMagicMethod;
        $this->isUncallable = $isUncallable;
        $this->handle = $handle;
        $this->returnValue = $returnValue;

        if ($callMagicMethod->isStatic()) {
            $this->mock = null;
            $this->callback = [
                $callMagicMethod->getDeclaringClass()->getName(),
                '__callStatic',
            ];
        } else {
            $this->mock = $handle->get();
            $this->callback = [$this->mock, '__call'];
        }
    }

    /**
     * Get the method.
     *
     * @return ReflectionMethod The method.
     */
    public function callMagicMethod(): ReflectionMethod
    {
        return $this->callMagicMethod;
    }

    /**
     * Returns true if uncallable.
     *
     * @return bool True if uncallable.
     */
    public function isUncallable(): bool
    {
        return $this->isUncallable;
    }

    /**
     * Get the name.
     *
     * @return string The name.
     */
    public function name(): string
    {
        return $this->name;
    }

    /**
     * Get the handle.
     *
     * @return Handle The handle.
     */
    public function handle(): Handle
    {
        return $this->handle;
    }

    /**
     * Get the mock.
     *
     * @return Mock|null The mock.
     */
    public function mock()
    {
        return $this->mock;
    }

    /**
     * Invoke this object.
     *
     * This method supports reference parameters.
     *
     * @param Arguments|array $arguments The arguments.
     *
     * @return mixed     The result of invocation.
     * @throws Throwable If an error occurs.
     */
    public function invokeWith($arguments = [])
    {
        if ($this->isUncallable) {
            return $this->returnValue;
        }

        if (!$arguments instanceof Arguments) {
            $arguments = new Arguments($arguments);
        }

        return $this->callMagicMethod
            ->invoke($this->mock, $this->name, $arguments);
    }

    private $name;
    private $callMagicMethod;
    private $isUncallable;
    private $handle;
    private $mock;
    private $returnValue;
}
