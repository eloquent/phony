<?php

declare(strict_types=1);

namespace Eloquent\Phony\Mock\Method;

use Eloquent\Phony\Invocation\WrappedInvocableTrait;
use Eloquent\Phony\Mock\Handle\Handle;
use Eloquent\Phony\Mock\Handle\StaticHandle;
use Eloquent\Phony\Mock\Mock;
use ReflectionMethod;

/**
 * Used for implementing wrapped methods.
 */
trait WrappedMethodTrait
{
    use WrappedInvocableTrait;

    /**
     * Get the method.
     *
     * @return ReflectionMethod The method.
     */
    public function method(): ReflectionMethod
    {
        return $this->method;
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

    private function constructWrappedMethod($method, $handle)
    {
        $this->method = $method;
        $this->handle = $handle;
        $this->name = $method->getName();

        if ($handle instanceof StaticHandle) {
            $this->mock = null;
            $this->callback = [
                $method->getDeclaringClass()->getName(),
                $this->name,
            ];
        } else {
            $this->mock = $handle->get();
            $this->callback = [$this->mock, $this->name];
        }
    }

    private $method;
    private $handle;
    private $mock;
    private $name;
}
