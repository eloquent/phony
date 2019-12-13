<?php

declare(strict_types=1);

namespace Eloquent\Phony\Mock\Builder\Method;

/**
 * Represents a collection of methods.
 */
class MethodDefinitionCollection
{
    /**
     * Construct a new custom method definition.
     *
     * @param array<string,MethodDefinition>   $methods      The methods.
     * @param array<int,TraitMethodDefinition> $traitMethods The trait methods.
     */
    public function __construct(array $methods, array $traitMethods)
    {
        $this->methodNames = [];
        $this->allMethods = $methods;
        $this->traitMethods = $traitMethods;
        $this->staticMethods = [];
        $this->methods = [];
        $this->publicStaticMethods = [];
        $this->publicMethods = [];
        $this->protectedStaticMethods = [];
        $this->protectedMethods = [];

        foreach ($methods as $name => $method) {
            $this->methodNames[strtolower($name)] = $name;

            $isStatic = $method->isStatic();
            $accessLevel = $method->accessLevel();
            $isPublic = 'public' === $accessLevel;

            if ($isStatic) {
                $this->staticMethods[$name] = $method;

                if ($isPublic) {
                    $this->publicStaticMethods[$name] = $method;
                } else {
                    $this->protectedStaticMethods[$name] = $method;
                }
            } else {
                $this->methods[$name] = $method;

                if ($isPublic) {
                    $this->publicMethods[$name] = $method;
                } else {
                    $this->protectedMethods[$name] = $method;
                }
            }
        }
    }

    /**
     * Get the canonical method name for the supplied method name.
     *
     * @param string $name The method name.
     *
     * @return string The canonical method name, or an empty string if no such method exists.
     */
    public function methodName(string $name): string
    {
        $name = strtolower($name);

        return $this->methodNames[$name] ?? '';
    }

    /**
     * Get the methods.
     *
     * @return array<string,MethodDefinition> The methods.
     */
    public function allMethods(): array
    {
        return $this->allMethods;
    }

    /**
     * Get the static methods.
     *
     * @return array<string,MethodDefinition> The methods.
     */
    public function staticMethods(): array
    {
        return $this->staticMethods;
    }

    /**
     * Get the instance methods.
     *
     * @return array<string,MethodDefinition> The methods.
     */
    public function methods(): array
    {
        return $this->methods;
    }

    /**
     * Get the public static methods.
     *
     * @return array<string,MethodDefinition> The methods.
     */
    public function publicStaticMethods(): array
    {
        return $this->publicStaticMethods;
    }

    /**
     * Get the public instance methods.
     *
     * @return array<string,MethodDefinition> The methods.
     */
    public function publicMethods(): array
    {
        return $this->publicMethods;
    }

    /**
     * Get the protected static methods.
     *
     * @return array<string,MethodDefinition> The methods.
     */
    public function protectedStaticMethods(): array
    {
        return $this->protectedStaticMethods;
    }

    /**
     * Get the protected instance methods.
     *
     * @return array<string,MethodDefinition> The methods.
     */
    public function protectedMethods(): array
    {
        return $this->protectedMethods;
    }

    /**
     * Get the trait methods.
     *
     * @return array<int,TraitMethodDefinition> The trait methods.
     */
    public function traitMethods(): array
    {
        return $this->traitMethods;
    }

    /**
     * @var array<string,string>
     */
    private $methodNames;

    /**
     * @var array<string,MethodDefinition>
     */
    private $allMethods;

    /**
     * @var array<int,TraitMethodDefinition>
     */
    private $traitMethods;

    /**
     * @var array<string,MethodDefinition>
     */
    private $staticMethods;

    /**
     * @var array<string,MethodDefinition>
     */
    private $methods;

    /**
     * @var array<string,MethodDefinition>
     */
    private $publicStaticMethods;

    /**
     * @var array<string,MethodDefinition>
     */
    private $publicMethods;

    /**
     * @var array<string,MethodDefinition>
     */
    private $protectedStaticMethods;

    /**
     * @var array<string,MethodDefinition>
     */
    private $protectedMethods;
}
