<?php

declare(strict_types=1);

namespace Eloquent\Phony\Mock;

use Eloquent\Phony\Mock\Builder\Method\MethodDefinition;
use Eloquent\Phony\Mock\Builder\Method\TraitMethodDefinition;
use Eloquent\Phony\Mock\Builder\MockDefinition;
use Eloquent\Phony\Reflection\FeatureDetector;
use Eloquent\Phony\Reflection\FunctionSignatureInspector;
use Eloquent\Phony\Sequencer\Sequencer;
use ReflectionMethod;

/**
 * Generates mock classes.
 */
class MockGenerator
{
    /**
     * Construct a new mock generator.
     *
     * @param Sequencer                  $labelSequencer     The label sequencer to use.
     * @param FunctionSignatureInspector $signatureInspector The function signature inspector to use.
     * @param FeatureDetector            $featureDetector    The feature detector to use.
     */
    public function __construct(
        Sequencer $labelSequencer,
        FunctionSignatureInspector $signatureInspector,
        FeatureDetector $featureDetector
    ) {
        $this->labelSequencer = $labelSequencer;
        $this->signatureInspector = $signatureInspector;

        $this->isReadOnlyPropertySupported =
            $featureDetector->isSupported('object.property.readonly');
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
                $className,
                $definition->methods()->publicStaticMethods(),
                $hasParentClass
            ) .
            $this->generateMagicCallStatic($definition, $className) .
            $this->generateStructors($definition, $hasParentClass) .
            $this->generateMethods(
                $className,
                $definition->methods()->publicMethods(),
                $hasParentClass
            ) .
            $this->generateMagicCall($definition, $className) .
            $this->generateMethods(
                $className,
                $definition->methods()->protectedStaticMethods(),
                $hasParentClass
            ) .
            $this->generateMethods(
                $className,
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

        $imports = <<<'EOD'
use Eloquent\Phony\Call\Arguments;
use Eloquent\Phony\Mock\Handle\InstanceHandle;
use Eloquent\Phony\Mock\Handle\StaticHandleRegistry;
use Eloquent\Phony\Mock\Mock;


EOD;

        $readonly = $definition->isReadOnly() ? 'readonly ' : '';
        $source = $namespace . $imports . $readonly . 'class ' . $className;

        $parentClassName = $definition->parentClassName();
        $interfaceNames = $definition->interfaceNames();
        $traitNames = $definition->traitNames();

        if ('' !== $parentClassName) {
            $source .= "\nextends \\" . $parentClassName;
        }

        $qualifiedInterfaceNames = [];

        foreach ($interfaceNames as $interfaceName) {
            $qualifiedInterfaceNames[] = "\\$interfaceName";
        }

        array_unshift($qualifiedInterfaceNames, 'Mock');
        $source .= "\nimplements " .
            implode(",\n           ", $qualifiedInterfaceNames);

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

    private function generateMagicCallStatic(
        MockDefinition $definition,
        string $className
    ): string {
        $methods = $definition->methods();
        $callStaticName = $methods->methodName('__callstatic');
        $methods = $methods->publicStaticMethods();

        if (!$callStaticName) {
            return '';
        }

        /** @var ReflectionMethod */
        $methodReflector = $methods[$callStaticName]->method();
        $returnsReference = $methodReflector->returnsReference() ? '&' : '';

        $result = self::VAR_PREFIX . 'result';

        $source = <<<EOD

    public static function {$returnsReference}__callStatic(
EOD;

        list($parameters, $returnType) =
            $this->signatureInspector->signature($methodReflector);
        $isFirst = true;
        $parameterNames = [];

        foreach ($parameters as $parameterName => $parameter) {
            if ($isFirst) {
                $isFirst = false;
            } else {
                $source .= ',';
            }

            $parameterNames[] = $parameterName;
            $parameterType = $parameter[0];

            if ('self ' === $parameterType) {
                $parameterType = '\\' . $className . ' ';
            }

            $source .= "\n        " .
                $parameterType .
                $parameter[1] .
                '$' . $parameterName .
                $parameter[3];
        }

        if ($returnType) {
            if ('self' === $returnType) {
                $returnType = '\\' . $className;
            }

            $source .= "\n    ) : " . $returnType . " {\n";
            $canReturn = 'never' !== $returnType && 'void' !== $returnType;
        } else {
            $source .= "\n    ) {\n";
            $canReturn = true;
        }

        $staticHandle = sprintf(
            self::STATIC_HANDLE,
            var_export(strtolower($className), true)
        );

        if ($canReturn) {
            $source .= <<<EOD
        $result = {$staticHandle}->spy(\${$parameterNames[0]})
            ->invokeWith(new Arguments(\${$parameterNames[1]}));

        return $result;
    }

EOD;
        } else {
            $source .= <<<EOD
        {$staticHandle}->spy(\${$parameterNames[0]})
            ->invokeWith(new Arguments(\${$parameterNames[1]}));
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
                ? 'parent::__destruct();'
                : '';

            $source .= <<<EOD

    public function __destruct()
    {
        if (isset(\$this->_handle)) {
            \$this->_handle->spy('__destruct')->invokeWith([]);
        } else {
            {$parentDestruct}
        }
    }

EOD;
        }

        return $source;
    }

    /**
     * @param array<string,MethodDefinition> $methods
     */
    private function generateMethods(
        string $className,
        array $methods,
        bool $hasParentClass
    ): string {
        $staticHandle = sprintf(
            self::STATIC_HANDLE,
            var_export(strtolower($className), true)
        );

        $arguments = self::VAR_PREFIX . 'arguments';
        $argumentCount = self::VAR_PREFIX . 'argumentCount';
        $result = self::VAR_PREFIX . 'result';
        $i = self::VAR_PREFIX . 'i';
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

            list($parameters, $returnType) =
                $this->signatureInspector->signature($methodReflector);

            if ($method->isCustom()) {
                $parameterName = null;

                foreach ($parameters as $parameterName => $parameter) {
                    break;
                }

                if ('phonySelf' === $parameterName) {
                    array_shift($parameters);
                }
            }

            $parameterCount = count($parameters);
            $variadicIndex = -1;
            $variadicReference = '';
            $variadicName = '';

            if (empty($parameters)) {
                $argumentPacking = '';
            } else {
                $argumentPacking = "\n";
                $index = -1;

                foreach ($parameters as $parameterName => $parameter) {
                    if ($parameter[2]) {
                        --$parameterCount;

                        $variadicIndex = ++$index;
                        $variadicReference = $parameter[1];
                        $variadicName = $parameterName;
                    } else {
                        $argumentPacking .=
                            "\n        if ($argumentCount > " .
                            ++$index .
                            ") {\n            {$arguments}[] = " .
                            $parameter[1] .
                            '$' . $parameterName .
                            ";\n        }";
                    }
                }
            }

            if ($returnType) {
                if ('self' === $returnType) {
                    $returnType = '\\' . $className;
                }

                $returnTypeSource = ' : ' . $returnType;
                $canReturn = 'never' !== $returnType && 'void' !== $returnType;
            } else {
                $returnTypeSource = '';
                $canReturn = true;
            }

            $isStatic = $method->isStatic() ? 'static ' : '';

            if ($isStatic) {
                $handle = $staticHandle;
            } else {
                $handle = '$this->_handle';
            }

            $body =
                "        $argumentCount = \\func_num_args();\n" .
                "        $arguments = [];" .
                $argumentPacking .
                "\n\n        for ($i = " .
                $parameterCount .
                "; $i < $argumentCount; ++$i) {\n";

            if ($variadicIndex > -1) {
                $body .= "            {$arguments}[] = $variadicReference\$" .
                    "{$variadicName}[$i - $variadicIndex];\n";
            } else {
                $body .= "            {$arguments}[] = \\func_get_arg($i);\n";
            }

            $body .=
                "        }\n\n        if (isset({$handle})) {\n";

            if ($canReturn) {
                $body .=
                    "            $result = {$handle}->spy" .
                    "(__FUNCTION__)->invokeWith(\n" .
                    '                new Arguments' .
                    "($arguments)\n            );\n\n" .
                    "            return $result;";
            } else {
                $body .=
                    "            {$handle}->spy" .
                    "(__FUNCTION__)->invokeWith(\n" .
                    '                new Arguments' .
                    "($arguments)\n            );";
            }

            $body .= "\n        }";

            if ($hasParentClass || $canReturn) {
                $body .= " else {\n            ";

                if ($canReturn) {
                    $body .= "$result = ";
                } else {
                    $body .= '';
                }

                if ($hasParentClass) {
                    $body .= "parent::$name(...$arguments);";
                } else {
                    $body .= 'null;';
                }

                if ($canReturn) {
                    $body .= "\n\n            return $result;";
                }

                $body .= "\n        }";
            }

            $returnsReference = $methodReflector->returnsReference() ? '&' : '';

            $source .= "\n    " .
                $method->accessLevel() .
                ' ' .
                $isStatic .
                'function ' .
                $returnsReference .
                $name;

            if (empty($parameters)) {
                $source .= '()' . $returnTypeSource . "\n    {\n";
            } else {
                $isFirst = true;

                foreach ($parameters as $parameterName => $parameter) {
                    if ($isFirst) {
                        $isFirst = false;
                        $source .= "(\n        ";
                    } else {
                        $source .= ",\n        ";
                    }

                    $parameterType = $parameter[0];

                    if ('self ' === $parameterType) {
                        $parameterType = '\\' . $className . ' ';
                    }

                    $source .= $parameterType .
                        $parameter[1] .
                        $parameter[2] .
                        '$' . $parameterName .
                        $parameter[3];
                }

                $source .= "\n    )" . $returnTypeSource . " {\n";
            }

            $source .= $body . "\n    }\n";
        }

        return $source;
    }

    private function generateMagicCall(
        MockDefinition $definition,
        string $className
    ): string {
        $methods = $definition->methods();
        $callName = $methods->methodName('__call');
        $methods = $methods->publicMethods();

        if (!$callName) {
            return '';
        }

        /** @var ReflectionMethod */
        $methodReflector = $methods[$callName]->method();
        $returnsReference = $methodReflector->returnsReference() ? '&' : '';

        $result = self::VAR_PREFIX . 'result';

        $source = <<<EOD

    public function {$returnsReference}__call(
EOD;
        list($parameters, $returnType) =
            $this->signatureInspector->signature($methodReflector);
        $isFirst = true;
        $parameterNames = [];

        foreach ($parameters as $parameterName => $parameter) {
            if ($isFirst) {
                $isFirst = false;
            } else {
                $source .= ',';
            }

            $parameterNames[] = $parameterName;
            $parameterType = $parameter[0];

            if ('self ' === $parameterType) {
                $parameterType = '\\' . $className . ' ';
            }

            $source .= "\n        " .
                $parameterType .
                $parameter[1] .
                '$' . $parameterName .
                $parameter[2];
        }

        if ($returnType) {
            if ('self' === $returnType) {
                $returnType = '\\' . $className;
            }

            $source .= "\n    ) : " . $returnType . " {\n";
            $canReturn = 'never' !== $returnType && 'void' !== $returnType;
        } else {
            $source .= "\n    ) {\n";
            $canReturn = true;
        }

        if ($canReturn) {
            $source .= <<<EOD
        $result = \$this->_handle->spy(\${$parameterNames[0]})
            ->invokeWith(new Arguments(\${$parameterNames[1]}));

        return $result;
    }

EOD;
        } else {
            $source .= <<<EOD
        \$this->_handle->spy(\${$parameterNames[0]})
            ->invokeWith(new Arguments(\${$parameterNames[1]}));
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
        Arguments $arguments
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
        Arguments $arguments
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
        Arguments \$arguments
    ) {{$parentCall}}

EOD;
            } else {
                $source .= <<<EOD

    private static function _callMagicStatic(
        \$name,
        Arguments \$arguments
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
        Arguments $arguments
    ) {
        return parent::$name(...$arguments->all());
    }

EOD;

            $parentClass = $types[strtolower($parentClassName)];

            if ($constructor = $parentClass->getConstructor()) {
                $constructorName = $constructor->getName();

                if ($constructor->isPrivate()) {
                    $parentClassNameQuoted = var_export($parentClassName, true);
                    $source .= <<<EOD

    private function _callParentConstructor(
        Arguments \$arguments
    ) {
        \$constructor = new ReflectionMethod($parentClassNameQuoted, "__construct");
        \$constructor->setAccessible(true);
        \$constructor->invokeArgs(\$this,\$arguments->all());
    }

EOD;
                } else {
                    $source .= <<<EOD

    private function _callParentConstructor(
        Arguments \$arguments
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
        Arguments \$arguments
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
        Arguments $arguments
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
        Arguments \$arguments
    ) {{$parentCall}}

EOD;
            } else {
                $source .= <<<EOD

    private function _callMagic(
        \$name,
        Arguments \$arguments
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
            list($isReadOnly, $type, $value) = $tuple;

            $initializer = $isReadOnly
                ? ''
                : ' = ' . (null === $value ? 'null' : var_export($value, true));

            $source .=
                "\n    public " .
                ($isReadOnly ? 'readonly ' : '') .
                ($type ? $type . ' ' : '') .
                '$' .
                $name .
                $initializer .
                ';';
        }

        $source .= "\n";

        if ($this->isReadOnlyPropertySupported) {
            $source .=  '    private readonly InstanceHandle $_handle;';
        } else {
            $source .=  '    private $_handle;';
        }

        return $source;
    }

    const VAR_PREFIX = "$\u{a4}";
    const NS_SEPARATOR = "\u{a6}";
    const METHOD_SEPARATOR = "\u{bb}";
    const STATIC_HANDLE = 'StaticHandleRegistry::$handles[%s]';

    /**
     * @var Sequencer
     */
    private $labelSequencer;

    /**
     * @var FunctionSignatureInspector
     */
    private $signatureInspector;

    /**
     * @var bool
     */
    private $isReadOnlyPropertySupported;
}
