<?php

declare(strict_types=1);

namespace Eloquent\Phony\Mock\Builder\Method;

use ReflectionFunctionAbstract;
use ReflectionMethod;

/**
 * Represents a real method definition.
 */
class RealMethodDefinition implements MethodDefinition
{
    /**
     * Construct a new real method definition.
     *
     * @param ReflectionMethod $method The method.
     * @param string           $name   The name.
     */
    public function __construct(ReflectionMethod $method, string $name)
    {
        $this->method = $method;
        $this->name = $name;
        $this->isCallable = !$this->method->isAbstract();
        $this->isStatic = $this->method->isStatic();

        if ($this->method->isPublic()) {
            $this->accessLevel = 'public';
        } else {
            $this->accessLevel = 'protected';
        }
    }

    /**
     * Returns true if this method is callable.
     *
     * @return bool True if this method is callable.
     */
    public function isCallable(): bool
    {
        return $this->isCallable;
    }

    /**
     * Returns true if this method is static.
     *
     * @return bool True if this method is static.
     */
    public function isStatic(): bool
    {
        return $this->isStatic;
    }

    /**
     * Returns true if this method is custom.
     *
     * @return bool True if this method is custom.
     */
    public function isCustom(): bool
    {
        return false;
    }

    /**
     * Get the access level.
     *
     * @return string The access level.
     */
    public function accessLevel(): string
    {
        return $this->accessLevel;
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
     * Get the method.
     *
     * @return ReflectionMethod The method.
     */
    public function method(): ReflectionFunctionAbstract
    {
        return $this->method;
    }

    /**
     * Get the callback.
     *
     * @return ?callable The callback, or null if this is a real method.
     */
    public function callback(): ?callable
    {
        return null;
    }

    /**
     * @var ReflectionMethod
     */
    private $method;

    /**
     * @var string
     */
    private $name;

    /**
     * @var bool
     */
    private $isCallable;

    /**
     * @var bool
     */
    private $isStatic;

    /**
     * @var string
     */
    private $accessLevel;
}
