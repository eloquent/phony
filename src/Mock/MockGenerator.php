<?php

declare(strict_types=1);

namespace Eloquent\Phony\Mock;

use Eloquent\Phony\Mock\Builder\Method\MethodDefinition;
use Eloquent\Phony\Mock\Builder\Method\TraitMethodDefinition;
use Eloquent\Phony\Mock\Builder\MockDefinition;
use Eloquent\Phony\Reflection\FunctionSignatureInspector;
use Eloquent\Phony\Sequencer\Sequencer;
use ReflectionMethod;
use ReflectionNamedType;

/**
 * Generates mock classes.
 */
class MockGenerator
{
    /**
     * Get the static instance of this class.
     *
     * @return self The static instance.
     */
    public static function instance(): self
    {
        if (!self::$instance) {
            self::$instance = new self(
                Sequencer::sequence('mock-class-label'),
                FunctionSignatureInspector::instance()
            );
        }

        return self::$instance;
    }

    /**
     * Construct a new mock generator.
     *
     * @param Sequencer                  $labelSequencer     The label sequencer to use.
     * @param FunctionSignatureInspector $signatureInspector The function signature inspector to use.
     */
    public function __construct(
        Sequencer $labelSequencer,
        FunctionSignatureInspector $signatureInspector
    ) {
        $this->labelSequencer = $labelSequencer;
        $this->signatureInspector = $signatureInspector;
    }

    /**
     * Generate a mock class name.
     *
     * @param MockDefinition $definition The definition.
     *
     * @return string The mock class name.
     */
    public function generateClassName(MockDefinition $definition): string
    {
        $className = $definition->className();

        if ('' !== $className) {
            return $className;
        }

        $className = 'PhonyMock';
        $parentClassName = $definition->parentClassName();

        if ('' !== $parentClassName) {
            $subject = $parentClassName;
        } elseif ($interfaceNames = $definition->interfaceNames()) {
            $subject = $interfaceNames[0];
        } elseif ($traitNames = $definition->traitNames()) {
            $subject = $traitNames[0];
        } else {
            $subject = null;
        }

        if (null !== $subject) {
            /** @var array<int,string> */
            $subjectAtoms = preg_split('/[_\\\\]/', $subject);
            $className .= '_' . array_pop($subjectAtoms);
        }

        $className .= '_' . $this->labelSequencer->next();

        return $className;
    }

    /**
     * Generate a mock class and return the source code.
     *
     * @param MockDefinition $definition The definition.
     * @param string         $className  The class name.
     *
     * @return string The source code.
     */
    public function generate(
        MockDefinition $definition,
        string $className = ''
    ): string {
        if ('' === $className) {
            $className = $this->generateClassName($definition);
        }

        $parentClassName = $definition->parentClassName();
        $hasParentClass = '' !== $parentClassName;

        $source = $this->generateHeader($definition, $className) .
            $this->generateConstants($definition) .
            $this->generateMethods(
                $definition->methods()->publicStaticMethods(),
                $hasParentClass
            ) .
            $this->generateMagicCallStatic($definition) .
            $this->generateStructors($definition, $hasParentClass) .
            $this->generateMethods(
                $definition->methods()->publicMethods(),
                $hasParentClass
            ) .
            $this->generateMagicCall($definition) .
            $this->generateMethods(
                $definition->methods()->protectedStaticMethods(),
                $hasParentClass
            ) .
            $this->generateMethods(
                $definition->methods()->protectedMethods(),
                $hasParentClass
            ) .
            $this->generateCallParentMethods(
                $definition,
                $hasParentClass,
                $parentClassName
            ) .
            $this->generateProperties($definition) .
            "\n}\n";

        // @codeCoverageIgnoreStart
        if (PHP_EOL !== "\n") {
            $source = str_replace("\n", PHP_EOL, $source);
        }
        // @codeCoverageIgnoreEnd

        return $source;
    }

    private function generateHeader(
        MockDefinition $definition,
        string $className
    ): string {
        $classNameParts = explode('\\', $className);

        if (count($classNameParts) > 1) {
            $className = array_pop($classNameParts);
            $namespace =
                'namespace ' . implode('\\', $classNameParts) . ";\n\n";
        } else {
            $namespace = '';
        }

        $source = $namespace . 'class ' . $className;

        $parentClassName = $definition->parentClassName();
        $interfaceNames = $definition->interfaceNames();
        $traitNames = $definition->traitNames();

        if ('' !== $parentClassName) {
            $source .= "\nextends \\" . $parentClassName;
        }

        array_unshift($interfaceNames, Mock::class);
        $source .= "\nimplements \\" .
            implode(",\n           \\", $interfaceNames);

        $source .= "\n{";

        if ($traitNames) {
            $traitName = array_shift($traitNames);
            $source .= "\n    use \\" . $traitName;

            foreach ($traitNames as $traitName) {
                $source .= ",\n        \\" . $traitName;
            }

            $source .= "\n    {";

            $methods = $definition->methods();

            foreach ($methods->traitMethods() as $method) {
                $typeName = $method->method()->getDeclaringClass()->getName();
                $methodName = $method->name();

                $source .= "\n        \\" .
                    $typeName .
                    '::' .
                    $methodName .
                    "\n            as private _callTrait_" .
                    str_replace('\\', self::NS_SEPARATOR, $typeName) .
                    self::METHOD_SEPARATOR .
                    $methodName .
                    ';';
            }

            $source .= "\n    }\n";
        }

        return $source;
    }

    private function generateConstants(MockDefinition $definition): string
    {
        $constants = $definition->customConstants();
        $source = '';

        if ($constants) {
            foreach ($constants as $name => $value) {
                $source .= "\n    const " .
                    $name .
                    ' = ' .
                    (null === $value ? 'null' : var_export($value, true)) .
                    ';';
            }

            $source .= "\n";
        }

        return $source;
    }

    private function generateMagicCallStatic(MockDefinition $definition): string
    {
        $methods = $definition->methods();
        $callStaticName = $methods->methodName('__callstatic');
        $methods = $methods->publicStaticMethods();

        if (!$callStaticName) {
            return '';
        }

        /** @var ReflectionMethod */
        $methodReflector = $methods[$callStaticName]->method();
        $returnsReference = $methodReflector->returnsReference() ? '&' : '';

        $source = <<<EOD

    public static function ${returnsReference}__callStatic(
EOD;

        $signature = $this->signatureInspector
            ->signature($methodReflector);
        $index = -1;

        foreach ($signature as $parameter) {
            if (-1 !== $index) {
                $source .= ',';
            }

            $source .= "\n        " .
                $parameter[0] .
                $parameter[1] .
                '$a' .
                ++$index .
                $parameter[3];
        }

        if ($methodReflector->hasReturnType()) {
            /** @var ReflectionNamedType */
            $type = $methodReflector->getReturnType();
            $isBuiltin = $type->isBuiltin();

            if ($type->allowsNull()) {
                $typeString = '?' . $type->getName();
            } else {
                $typeString = $type->getName();
            }

            if ('self' === $typeString) {
                $typeString = $methodReflector->getDeclaringClass()->getName();
            }

            if ($isBuiltin) {
                $source .= "\n    ) : " . $typeString . " {\n";
            } elseif (0 === strpos($typeString, '?')) {
                $source .= "\n    ) : ?\\" . substr($typeString, 1) . " {\n";
            } else {
                $source .= "\n    ) : \\" . $typeString . " {\n";
            }

            $isVoidReturn = $isBuiltin && 'void' === $typeString;
        } else {
            $source .= "\n    ) {\n";
            $isVoidReturn = false;
        }

        if ($isVoidReturn) {
            $source .= <<<'EOD'
        self::$_staticHandle->spy($a0)
            ->invokeWith(new \Eloquent\Phony\Call\Arguments($a1));
    }

EOD;
        } else {
            $source .= <<<'EOD'
        $result = self::$_staticHandle->spy($a0)
            ->invokeWith(new \Eloquent\Phony\Call\Arguments($a1));

        return $result;
    }

EOD;
        }

        return $source;
    }

    private function generateStructors(
        MockDefinition $definition,
        bool $hasParentClass
    ): string {
        $constructor = null;
        $destructor = null;

        foreach ($definition->types() as $name => $type) {
            if (!$constructor) {
                $constructor = $type->getConstructor();

                if ($constructor && $constructor->isFinal()) {
                    return '';
                }
            }

            if (!$destructor && $type->hasMethod('__destruct')) {
                $destructor = $type->getMethod('__destruct');
            }
        }

        $source = '';

        if ($constructor) {
            $source .= <<<'EOD'

    public function __construct()
    {
    }

EOD;
        }

        if ($destructor) {
            $parentDestruct = $hasParentClass
                ? "parent::__destruct();\n\n            "
                : '';

            $source .= <<<EOD

    public function __destruct()
    {
        if (!\$this->_handle) {
            ${parentDestruct}return;
        }

        \$this->_handle->spy('__destruct')->invokeWith([]);
    }

EOD;
        }

        return $source;
    }

    /**
     * @param array<string,MethodDefinition> $methods
     */
    private function generateMethods(
        array $methods,
        bool $hasParentClass
    ): string {
        $source = '';

        foreach ($methods as $method) {
            $name = $method->name();
            $nameLower = strtolower($name);
            $methodReflector = $method->method();

            switch ($nameLower) {
                case '__construct':
                case '__destruct':
                case '__call':
                case '__callstatic':
                    continue 2;
            }

            $signature = $this->signatureInspector->signature($methodReflector);

            if ($method->isCustom()) {
                $parameterName = null;

                foreach ($signature as $parameterName => $parameter) {
                    break;
                }

                if ('phonySelf' === $parameterName) {
                    array_shift($signature);
                }
            }

            $parameterCount = count($signature);
            $variadicIndex = -1;
            $variadicReference = '';

            if (empty($signature)) {
                $argumentPacking = '';
            } else {
                $argumentPacking = "\n";
                $index = -1;

                foreach ($signature as $parameter) {
                    if ($parameter[2]) {
                        --$parameterCount;

                        $variadicIndex = ++$index;
                        $variadicReference = $parameter[1];
                    } else {
                        $argumentPacking .=
                            "\n        if (\$argumentCount > " .
                            ++$index .
                            ") {\n            \$arguments[] = " .
                            $parameter[1] .
                            '$a' .
                            $index .
                            ";\n        }";
                    }
                }
            }

            if ($methodReflector->hasReturnType()) {
                /** @var ReflectionNamedType */
                $type = $methodReflector->getReturnType();
                $isBuiltin = $type->isBuiltin();

                if ($type->allowsNull()) {
                    $typeString = '?' . $type->getName();
                } else {
                    $typeString = $type->getName();
                }

                if ('self' === $typeString) {
                    /** @var ReflectionMethod */
                    $methodReflectorMethod = $methodReflector;
                    $typeString =
                        $methodReflectorMethod->getDeclaringClass()->getName();
                }

                if ($isBuiltin) {
                    $returnType = ' : ' . $typeString;
                } elseif (0 === strpos($typeString, '?')) {
                    $returnType = ' : ?\\' . substr($typeString, 1);
                } else {
                    $returnType = ' : \\' . $typeString;
                }

                $isVoidReturn = $isBuiltin && 'void' === $typeString;
            } else {
                $returnType = '';
                $isVoidReturn = false;
            }

            $isStatic = $method->isStatic() ? 'static ' : '';

            if ($isStatic) {
                $handle = 'self::$_staticHandle';
            } else {
                $handle = '$this->_handle';
            }

            $body =
                "        \$argumentCount = \\func_num_args();\n" .
                '        $arguments = [];' .
                $argumentPacking .
                "\n\n        for (\$i = " .
                $parameterCount .
                "; \$i < \$argumentCount; ++\$i) {\n";

            if ($variadicIndex > -1) {
                $body .= "            \$arguments[] = $variadicReference\$a" .
                    "${variadicIndex}[\$i - $variadicIndex];\n";
            } else {
                $body .= "            \$arguments[] = \\func_get_arg(\$i);\n";
            }

            $body .=
                "        }\n\n        if (!${handle}) {\n";

            if ($isVoidReturn) {
                $resultAssign = '';
            } else {
                $resultAssign = '$result = ';
            }

            if ($hasParentClass) {
                $resultExpression = "parent::$name(...\$arguments)";
            } else {
                $resultExpression = 'null';
            }

            $body .=  <<<EOD
            $resultAssign$resultExpression;
EOD;

            if ($isVoidReturn) {
                $body .=
                    "\n\n            return;\n        }\n\n" .
                    "        ${handle}->spy" .
                    "(__FUNCTION__)->invokeWith(\n" .
                    '            new \Eloquent\Phony\Call\Arguments' .
                    "(\$arguments)\n        );";
            } else {
                $body .=
                    "\n\n            return \$result;\n        }\n\n" .
                    "        \$result = ${handle}->spy" .
                    "(__FUNCTION__)->invokeWith(\n" .
                    '            new \Eloquent\Phony\Call\Arguments' .
                    "(\$arguments)\n        );\n\n" .
                    '        return $result;';
            }

            $returnsReference = $methodReflector->returnsReference() ? '&' : '';

            $source .= "\n    " .
                $method->accessLevel() .
                ' ' .
                $isStatic .
                'function ' .
                $returnsReference .
                $name;

            if (empty($signature)) {
                $source .= '()' . $returnType . "\n    {\n";
            } else {
                $index = -1;
                $isFirst = true;

                foreach ($signature as $parameter) {
                    if ($isFirst) {
                        $isFirst = false;
                        $source .= "(\n        ";
                    } else {
                        $source .= ",\n        ";
                    }

                    $source .= $parameter[0] .
                        $parameter[1] .
                        $parameter[2] .
                        '$a' .
                        ++$index .
                        $parameter[3];
                }

                $source .= "\n    )" . $returnType . " {\n";
            }

            $source .= $body . "\n    }\n";
        }

        return $source;
    }

    private function generateMagicCall(MockDefinition $definition): string
    {
        $methods = $definition->methods();
        $callName = $methods->methodName('__call');
        $methods = $methods->publicMethods();

        if (!$callName) {
            return '';
        }

        /** @var ReflectionMethod */
        $methodReflector = $methods[$callName]->method();
        $returnsReference = $methodReflector->returnsReference() ? '&' : '';

        $source = <<<EOD

    public function ${returnsReference}__call(
EOD;
        $signature = $this->signatureInspector->signature($methodReflector);
        $index = -1;

        foreach ($signature as $parameter) {
            if (-1 !== $index) {
                $source .= ',';
            }

            $source .= "\n        " .
                $parameter[0] .
                $parameter[1] .
                '$a' .
                ++$index .
                $parameter[2];
        }

        if ($methodReflector->hasReturnType()) {
            /** @var ReflectionNamedType */
            $type = $methodReflector->getReturnType();
            $isBuiltin = $type->isBuiltin();

            if ($type->allowsNull()) {
                $typeString = '?' . $type->getName();
            } else {
                $typeString = $type->getName();
            }

            if ('self' === $typeString) {
                $typeString = $methodReflector->getDeclaringClass()->getName();
            }

            if ($isBuiltin) {
                $source .= "\n    ) : " . $typeString . " {\n";
            } elseif (0 === strpos($typeString, '?')) {
                $source .= "\n    ) : ?\\" . substr($typeString, 1) . " {\n";
            } else {
                $source .= "\n    ) : \\" . $typeString . " {\n";
            }

            $isVoidReturn = $isBuiltin && 'void' === $typeString;
        } else {
            $source .= "\n    ) {\n";
            $isVoidReturn = false;
        }

        if ($isVoidReturn) {
            $source .= <<<'EOD'
        $this->_handle->spy($a0)
            ->invokeWith(new \Eloquent\Phony\Call\Arguments($a1));
    }

EOD;
        } else {
            $source .= <<<'EOD'
        $result = $this->_handle->spy($a0)
            ->invokeWith(new \Eloquent\Phony\Call\Arguments($a1));

        return $result;
    }

EOD;
        }

        return $source;
    }

    private function generateCallParentMethods(
        MockDefinition $definition,
        bool $hasParentClass,
        string $parentClassName
    ): string {
        $methods = $definition->methods();
        $traitNames = $definition->traitNames();
        $hasTraits = (bool) $traitNames;
        $constructor = null;
        $types = $definition->types();
        $source = '';

        if ($hasParentClass) {
            $source .= <<<'EOD'

    private static function _callParentStatic(
        $name,
        \Eloquent\Phony\Call\Arguments $arguments
    ) {
        return parent::$name(...$arguments->all());
    }

EOD;
        }

        if ($hasTraits) {
            $source .= <<<'EOD'

    private static function _callTraitStatic(
        $traitName,
        $name,
        \Eloquent\Phony\Call\Arguments $arguments
    ) {
        $name = '_callTrait_' .
            \str_replace('\\', "\u{a6}", $traitName) .
            "\u{bb}" .
            $name;

        return self::$name(...$arguments->all());
    }

EOD;
        }

        $name = $methods->methodName('__callstatic');

        if ($name) {
            $methodName = null;

            if ($hasTraits) {
                $methodsByName = $methods->staticMethods();

                if (
                    isset($methodsByName[$name]) &&
                    $methodsByName[$name] instanceof TraitMethodDefinition
                ) {
                    $traitName = $methodsByName[$name]
                        ->method()->getDeclaringClass()->getName();
                    $methodName = var_export(
                        '_callTrait_' .
                            \str_replace('\\', self::NS_SEPARATOR, $traitName) .
                            self::METHOD_SEPARATOR .
                            $name,
                        true
                    );
                }
            }

            if (null === $methodName) {
                $parentCall = $hasParentClass
                    ? "\n        return parent::__callStatic" .
                        "(\$name, \$arguments->all());\n    "
                    : '';

                $source .= <<<EOD

    private static function _callMagicStatic(
        \$name,
        \Eloquent\Phony\Call\Arguments \$arguments
    ) {{$parentCall}}

EOD;
            } else {
                $source .= <<<EOD

    private static function _callMagicStatic(
        \$name,
        \Eloquent\Phony\Call\Arguments \$arguments
    ) {
        \$methodName = $methodName;

        return self::\$methodName(\$name, \$arguments->all());
    }

EOD;
            }
        }

        if ($hasParentClass) {
            $source .= <<<'EOD'

    private function _callParent(
        $name,
        \Eloquent\Phony\Call\Arguments $arguments
    ) {
        return parent::$name(...$arguments->all());
    }

EOD;

            $parentClass = $types[strtolower($parentClassName)];

            if ($constructor = $parentClass->getConstructor()) {
                $constructorName = $constructor->getName();

                if ($constructor->isPrivate()) {
                    $source .= <<<EOD

    private function _callParentConstructor(
        \Eloquent\Phony\Call\Arguments \$arguments
    ) {
        \$constructor = function () use (\$arguments) {
            \call_user_func_array(
                [\$this, 'parent::$constructorName'],
                \$arguments->all()
            );
        };
        \$constructor = \$constructor->bindTo(\$this, '$parentClassName');
        \$constructor();
    }

EOD;
                } else {
                    $source .= <<<EOD

    private function _callParentConstructor(
        \Eloquent\Phony\Call\Arguments \$arguments
    ) {
        parent::$constructorName(...\$arguments->all());
    }

EOD;
                }
            }
        }

        if ($hasTraits) {
            if (!$constructor) {
                $constructorTraitName = null;

                foreach ($traitNames as $traitName) {
                    $trait = $types[strtolower($traitName)];

                    if ($traitConstructor = $trait->getConstructor()) {
                        $constructor = $traitConstructor;
                        $constructorTraitName = $trait->getName();
                    }
                }

                if ($constructor) {
                    /** @var class-string $constructorTraitNameClassName */
                    $constructorTraitNameClassName = $constructorTraitName;

                    $constructorName = '_callTrait_' .
                        \str_replace(
                            '\\',
                            self::NS_SEPARATOR,
                            $constructorTraitNameClassName
                        ) .
                        self::METHOD_SEPARATOR .
                        $constructor->getName();

                    $source .= <<<EOD

    private function _callParentConstructor(
        \Eloquent\Phony\Call\Arguments \$arguments
    ) {
        \$this->$constructorName(...\$arguments->all());
    }

EOD;
                }
            }

            $source .= <<<'EOD'

    private function _callTrait(
        $traitName,
        $name,
        \Eloquent\Phony\Call\Arguments $arguments
    ) {
        $name = '_callTrait_' .
            \str_replace('\\', "\u{a6}", $traitName) .
            "\u{bb}" .
            $name;

        return $this->$name(...$arguments->all());
    }

EOD;
        }

        $name = $methods->methodName('__call');

        if ($name) {
            $methodName = null;

            if ($hasTraits) {
                $methodsByName = $methods->methods();

                if (
                    isset($methodsByName[$name]) &&
                    $methodsByName[$name] instanceof TraitMethodDefinition
                ) {
                    $traitName = $methodsByName[$name]
                        ->method()->getDeclaringClass()->getName();
                    $methodName = var_export(
                        '_callTrait_' .
                            \str_replace('\\', self::NS_SEPARATOR, $traitName) .
                            self::METHOD_SEPARATOR .
                            $name,
                        true
                    );
                }
            }

            if (null === $methodName) {
                $parentCall = $hasParentClass
                    ? "\n        return parent::__call" .
                        "(\$name, \$arguments->all());\n    "
                    : '';

                $source .= <<<EOD

    private function _callMagic(
        \$name,
        \Eloquent\Phony\Call\Arguments \$arguments
    ) {{$parentCall}}

EOD;
            } else {
                $source .= <<<EOD

    private function _callMagic(
        \$name,
        \Eloquent\Phony\Call\Arguments \$arguments
    ) {
        \$methodName = $methodName;

        return \$this->\$methodName(\$name, \$arguments->all());
    }

EOD;
            }
        }

        return $source;
    }

    private function generateProperties(MockDefinition $definition): string
    {
        $staticProperties = $definition->customStaticProperties();
        $properties = $definition->customProperties();
        $source = '';

        foreach ($staticProperties as $name => $tuple) {
            list($type, $value) = $tuple;

            $source .=
                "\n    public static " .
                ($type ? $type . ' ' : '') .
                '$' .
                $name .
                ' = ' .
                (null === $value ? 'null' : var_export($value, true)) .
                ';';
        }

        foreach ($properties as $name => $tuple) {
            list($type, $value) = $tuple;

            $source .=
                "\n    public " .
                ($type ? $type . ' ' : '') .
                '$' .
                $name .
                ' = ' .
                (null === $value ? 'null' : var_export($value, true)) .
                ';';
        }

        $methods = $definition->methods()->allMethods();
        $uncallableMethodNames = [];
        $traitMethodNames = [];

        foreach ($methods as $methodName => $method) {
            $methodName = strtolower($methodName);

            if (!$method->isCallable()) {
                $uncallableMethodNames[$methodName] = true;
            } elseif ($method instanceof TraitMethodDefinition) {
                $traitMethodNames[$methodName] =
                    $method->method()->getDeclaringClass()->getName();
            }
        }

        $source .= "\n    private static \$_uncallableMethods = ";

        if (empty($uncallableMethodNames)) {
            $source .= '[]';
        } else {
            $source .= var_export($uncallableMethodNames, true);
        }

        $source .= ";\n    private static \$_traitMethods = ";

        if (empty($traitMethodNames)) {
            $source .= '[]';
        } else {
            $source .= var_export($traitMethodNames, true);
        }

        $source .= ";\n" .
            "    private static \$_customMethods = [];\n" .
            "    private static \$_staticHandle;\n" .
            '    private $_handle;';

        return $source;
    }

    const NS_SEPARATOR = "\u{a6}";
    const METHOD_SEPARATOR = "\u{bb}";

    /**
     * @var ?self
     */
    private static $instance;

    /**
     * @var Sequencer
     */
    private $labelSequencer;

    /**
     * @var FunctionSignatureInspector
     */
    private $signatureInspector;
}
