<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2015 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Mock\Generator;

use Eloquent\Phony\Feature\FeatureDetector;
use Eloquent\Phony\Feature\FeatureDetectorInterface;
use Eloquent\Phony\Mock\Builder\Definition\Method\MethodDefinitionInterface;
use Eloquent\Phony\Mock\Builder\Definition\Method\TraitMethodDefinitionInterface;
use Eloquent\Phony\Mock\Builder\Definition\MockDefinitionInterface;
use Eloquent\Phony\Reflection\FunctionSignatureInspector;
use Eloquent\Phony\Reflection\FunctionSignatureInspectorInterface;
use Eloquent\Phony\Sequencer\Sequencer;
use Eloquent\Phony\Sequencer\SequencerInterface;

/**
 * Generates mock classes.
 *
 * @internal
 */
class MockGenerator implements MockGeneratorInterface
{
    /**
     * Get the static instance of this generator.
     *
     * @return MockGeneratorInterface The static generator.
     */
    public static function instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Construct a new mock generator.
     *
     * @param SequencerInterface|null                  $labelSequencer     The label sequencer to use.
     * @param FunctionSignatureInspectorInterface|null $signatureInspector The function signature inspector to use.
     * @param FeatureDetectorInterface|null            $featureDetector    The feature detector to use.
     */
    public function __construct(
        SequencerInterface $labelSequencer = null,
        FunctionSignatureInspectorInterface $signatureInspector = null,
        FeatureDetectorInterface $featureDetector = null
    ) {
        if (null === $labelSequencer) {
            $labelSequencer = Sequencer::sequence('mock-class-label');
        }
        if (null === $signatureInspector) {
            $signatureInspector = FunctionSignatureInspector::instance();
        }
        if (null === $featureDetector) {
            $featureDetector = FeatureDetector::instance();
        }

        $this->labelSequencer = $labelSequencer;
        $this->signatureInspector = $signatureInspector;
        $this->featureDetector = $featureDetector;

        $this->isClosureBindingSupported =
            $this->featureDetector->isSupported('closure.bind');
    }

    /**
     * Get the label sequencer.
     *
     * @return SequencerInterface The label sequencer.
     */
    public function labelSequencer()
    {
        return $this->labelSequencer;
    }

    /**
     * Get the function signature inspector.
     *
     * @return FunctionSignatureInspectorInterface The function signature inspector.
     */
    public function signatureInspector()
    {
        return $this->signatureInspector;
    }

    /**
     * Get the feature detector.
     *
     * @return FeatureDetectorInterface The feature detector.
     */
    public function featureDetector()
    {
        return $this->featureDetector;
    }

    /**
     * Generate a mock class name.
     *
     * @param MockDefinitionInterface $definition The definition.
     *
     * @return string The mock class name.
     */
    public function generateClassName(MockDefinitionInterface $definition)
    {
        $className = $definition->className();

        if (null !== $className) {
            return $className;
        }

        $className = 'PhonyMock';
        $parentClassName = $definition->parentClassName();

        if (null !== $parentClassName) {
            $subject = $parentClassName;
        } elseif ($interfaceNames = $definition->interfaceNames()) {
            $subject = $interfaceNames[0];
        } elseif ($traitNames = $definition->traitNames()) {
            $subject = $traitNames[0];
        } else {
            $subject = null;
        }

        if ($subject) {
            $subjectAtoms = preg_split('/[_\\\\]/', $subject);
            $className .= '_' . array_pop($subjectAtoms);
        }

        $className .= '_' . $this->labelSequencer->next();

        return $className;
    }

    /**
     * Generate a mock class and return the source code.
     *
     * @param MockDefinitionInterface $definition The definition.
     * @param string|null             $className  The class name.
     *
     * @return string The source code.
     */
    public function generate(
        MockDefinitionInterface $definition,
        $className = null
    ) {
        if (null === $className) {
            $className = $this->generateClassName($definition);
        }

        return $this->generateHeader($definition, $className) .
            $this->generateConstants($definition) .
            $this->generateMethods(
                $definition->methods()->publicStaticMethods()
            ) .
            $this->generateMagicCallStatic($definition) .
            $this->generateConstructors($definition) .
            $this->generateMethods($definition->methods()->publicMethods()) .
            $this->generateMagicCall($definition) .
            $this->generateMethods(
                $definition->methods()->protectedStaticMethods()
            ) .
            $this->generateMethods($definition->methods()->protectedMethods()) .
            $this->generateCallParentMethods($definition) .
            $this->generateProperties($definition) .
            "\n}\n";
    }

    /**
     * Generate the class header.
     *
     * @param MockDefinitionInterface $definition The definition.
     * @param string                  $className  The class name.
     *
     * @return string The source code.
     */
    protected function generateHeader(
        MockDefinitionInterface $definition,
        $className
    ) {
        if ($typeNames = $definition->typeNames()) {
            $usedTypes = "\n *";

            foreach ($typeNames as $typeName) {
                $usedTypes .= "\n * @uses \\" . $typeName;
            }
        } else {
            $usedTypes = '';
        }

        $classNameParts = explode('\\', $className);

        if (count($classNameParts) > 1) {
            $className = array_pop($classNameParts);
            $namespace = 'namespace ' . implode('\\', $classNameParts) .
                ";\n\n";
        } else {
            $namespace = '';
        }

        $source = $namespace . 'class ' . $className;

        $parentClassName = $definition->parentClassName();
        $interfaceNames = $definition->interfaceNames();
        $traitNames = $definition->traitNames();

        if (null !== $parentClassName) {
            $source .= "\nextends \\" . $parentClassName;
        }

        array_unshift($interfaceNames, 'Eloquent\Phony\Mock\MockInterface');
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
                    str_replace(
                        '\\',
                        "\xc2\xa6",
                        $typeName
                    ) .
                    "\xc2\xbb" .
                    $methodName .
                    ';';
            }

            $source .= "\n    }\n";
        }

        return $source;
    }

    /**
     * Generate the class constants.
     *
     * @param MockDefinitionInterface $definition The definition.
     *
     * @return string The source code.
     */
    protected function generateConstants(MockDefinitionInterface $definition)
    {
        $constants = $definition->customConstants();
        $source = '';

        if ($constants) {
            foreach ($constants as $name => $value) {
                $source .= "\n    const " .
                    $name .
                    ' = ' .
                    (null === $value ? 'null' : $this->renderValue($value)) .
                    ';';
            }

            $source .= "\n";
        }

        return $source;
    }

    /**
     * Generate the __callStatic() method.
     *
     * @param MockDefinitionInterface $definition The definition.
     *
     * @return string The source code.
     */
    protected function generateMagicCallStatic(
        MockDefinitionInterface $definition
    ) {
        $methods = $definition->methods();
        $callStaticName = $methods->methodName('__callstatic');
        $methods = $methods->publicStaticMethods();

        if (null === $callStaticName) {
            return '';
        }

        $source = <<<'EOD'

    public static function __callStatic(
EOD;

        $signature = $this->signatureInspector
            ->signature($methods[$callStaticName]->method());
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

        $source .= <<<'EOD'

    ) {
        return self::$_staticProxy->spy($a0)
            ->invokeWith(new \Eloquent\Phony\Call\Argument\Arguments($a1));
    }

EOD;

        return $source;
    }

    /**
     * Generate the constructors.
     *
     * @param MockDefinitionInterface $definition The definition.
     *
     * @return string The source code.
     */
    protected function generateConstructors(MockDefinitionInterface $definition)
    {
        $constructor = null;

        foreach ($definition->types() as $type) {
            $constructor = $type->getConstructor();

            if ($constructor) {
                break;
            }
        }

        if (!$constructor) {
            return '';
        }

        return <<<'EOD'

    public function __construct()
    {
    }

EOD;
    }

    /**
     * Generate the supplied methods.
     *
     * @param array<string,MethodDefinitionInterface> The methods.
     *
     * @return string The source code.
     */
    protected function generateMethods(array $methods)
    {
        $source = '';

        foreach ($methods as $method) {
            $name = $method->name();
            $nameLower = strtolower($name);

            switch ($nameLower) {
                case '__construct':
                case '__call':
                case '__callstatic':
                    continue 2;
            }

            $signature =
                $this->signatureInspector->signature($method->method());

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

            if ($signature) {
                $argumentPacking = "\n";
                $index = -1;

                foreach ($signature as $parameter) {
                    if ($parameter[2]) {
                        --$parameterCount;

                        $variadicIndex = ++$index;
                        $variadicReference = $parameter[1];
                    } else {
                        $argumentPacking .= "\n        if (\$argumentCount > " .
                            ++$index .
                            ") {\n            \$arguments[] = " .
                            $parameter[1] .
                            '$a' .
                            $index .
                            ";\n        }";
                    }
                }
            } else {
                $argumentPacking = '';
            }

            $isStatic = $method->isStatic();

            if ($isStatic) {
                $proxy = 'self::$_staticProxy';
            } else {
                $proxy = '$this->_proxy';
            }

            if ($variadicIndex > -1) {
                $body = "        \$argumentCount = \\func_num_args();\n" .
                    "        \$arguments = array();" .
                    $argumentPacking .
                    "\n\n        for (\$i = " .
                    $parameterCount .
                    "; \$i < \$argumentCount; ++\$i) {\n" .
                    "            \$arguments[] = $variadicReference\$a" .
                    "${variadicIndex}[\$i - $variadicIndex];\n" .
                    "        }\n\n        return ${proxy}->spy" .
                    "(__FUNCTION__)->invokeWith(\n            " .
                    "new \Eloquent\Phony\Call\Argument\Arguments" .
                    "(\$arguments)\n        );";
            } else {
                $body = "        \$argumentCount = \\func_num_args();\n" .
                    "        \$arguments = array();" .
                    $argumentPacking .
                    "\n\n        for (\$i = " .
                    $parameterCount .
                    "; \$i < \$argumentCount; ++\$i) {\n" .
                    "            \$arguments[] = \\func_get_arg(\$i);\n" .
                    "        }\n\n        return ${proxy}->spy" .
                    "(__FUNCTION__)->invokeWith(\n            " .
                    "new \Eloquent\Phony\Call\Argument\Arguments" .
                    "(\$arguments)\n        );";
            }

            $source .= "\n    " .
                $method->accessLevel() .
                ' ' .
                ($isStatic ? 'static ' : '') .
                'function ' .
                $name;

            if ($signature) {
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

                $source .= "\n    ) {\n";
            } else {
                $source .= "()\n    {\n";
            }

            $source .= $body . "\n    }\n";
        }

        return $source;
    }

    /**
     * Generate the __call() method.
     *
     * @param MockDefinitionInterface $definition The definition.
     *
     * @return string The source code.
     */
    protected function generateMagicCall(MockDefinitionInterface $definition)
    {
        $methods = $definition->methods();
        $callName = $methods->methodName('__call');
        $methods = $methods->publicMethods();

        if (null === $callName) {
            return '';
        }

        $source = <<<'EOD'

    public function __call(
EOD;

        $signature = $this->signatureInspector
            ->signature($methods[$callName]->method());
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

        $source .= <<<'EOD'

    ) {
        return $this->_proxy->spy($a0)
            ->invokeWith(new \Eloquent\Phony\Call\Argument\Arguments($a1));
    }

EOD;

        return $source;
    }

    /**
     * Generate the call parent methods.
     *
     * @param MockDefinitionInterface $definition The definition.
     *
     * @return string The source code.
     */
    protected function generateCallParentMethods(
        MockDefinitionInterface $definition
    ) {
        $traitNames = $definition->traitNames();
        $hasTraits = (boolean) $traitNames;
        $parentClassName = $definition->parentClassName();
        $hasParentClass = null !== $parentClassName;
        $constructor = null;
        $types = $definition->types();
        $source = '';

        if ($hasParentClass) {
            $source .= <<<'EOD'

    private static function _callParentStatic(
        $name,
        \Eloquent\Phony\Call\Argument\ArgumentsInterface $arguments
    ) {
        return \call_user_func_array(
            array(__CLASS__, 'parent::' . $name),
            $arguments->all()
        );
    }

EOD;
        }

        if ($hasTraits) {
            $source .= <<<'EOD'

    private static function _callTraitStatic(
        $traitName,
        $name,
        \Eloquent\Phony\Call\Argument\ArgumentsInterface $arguments
    ) {
        return \call_user_func_array(
            array(
                __CLASS__,
                '_callTrait_' .
                    \str_replace('\\', "\xc2\xa6", $traitName) .
                    "\xc2\xbb" .
                    $name,
            ),
            $arguments->all()
        );
    }

EOD;
        }

        if (null !== $definition->methods()->methodName('__callstatic')) {
            $source .= <<<'EOD'

    private static function _callMagicStatic(
        $name,
        \Eloquent\Phony\Call\Argument\ArgumentsInterface $arguments
    ) {
        return self::$_staticProxy
            ->spy('__callStatic')->invoke($name, $arguments->all());
    }

EOD;
        }

        if ($hasParentClass) {
            $source .= <<<'EOD'

    private function _callParent(
        $name,
        \Eloquent\Phony\Call\Argument\ArgumentsInterface $arguments
    ) {
        return \call_user_func_array(
            array($this, 'parent::' . $name),
            $arguments->all()
        );
    }

EOD;

            $parentClass = $types[$parentClassName];

            if ($constructor = $parentClass->getConstructor()) {
                $constructorName = $constructor->getName();

                if ($constructor->isPrivate()) {
                    if ($this->isClosureBindingSupported) {
                        $source .= <<<EOD

    private function _callParentConstructor(
        \Eloquent\Phony\Call\Argument\ArgumentsInterface \$arguments
    ) {
        \$constructor = function () use (\$arguments) {
            \call_user_func_array(
                array(\$this, 'parent::$constructorName'),
                \$arguments->all()
            );
        };
        \$constructor = \$constructor->bindTo(\$this, '$parentClassName');
        \$constructor();
    }

EOD;
                    }
                } else {
                    $source .= <<<EOD

    private function _callParentConstructor(
        \Eloquent\Phony\Call\Argument\ArgumentsInterface \$arguments
    ) {
        \call_user_func_array(
            array(\$this, 'parent::$constructorName'),
            \$arguments->all()
        );
    }

EOD;
                }
            }
        }

        if ($hasTraits) {
            if (!$constructor) {
                $constructorTraitName = null;

                foreach ($traitNames as $traitName) {
                    $trait = $types[$traitName];

                    if ($traitConstructor = $trait->getConstructor()) {
                        $constructor = $traitConstructor;
                        $constructorTraitName = $trait->getName();
                    }
                }

                if ($constructor) {
                    $constructorName = '_callTrait_' .
                        \str_replace('\\', "\xc2\xa6", $constructorTraitName) .
                        "\xc2\xbb" .
                        $constructor->getName();

                    $source .= <<<EOD

    private function _callParentConstructor(
        \Eloquent\Phony\Call\Argument\ArgumentsInterface \$arguments
    ) {
        \call_user_func_array(
            array(
                \$this,
                '$constructorName',
            ),
            \$arguments->all()
        );
    }

EOD;
                }
            }

            $source .= <<<'EOD'

    private function _callTrait(
        $traitName,
        $name,
        \Eloquent\Phony\Call\Argument\ArgumentsInterface $arguments
    ) {
        return \call_user_func_array(
            array(
                $this,
                '_callTrait_' .
                    \str_replace('\\', "\xc2\xa6", $traitName) .
                    "\xc2\xbb" .
                    $name,
            ),
            $arguments->all()
        );
    }

EOD;
        }

        if (null !== $definition->methods()->methodName('__call')) {
            $source .= <<<'EOD'

    private function _callMagic(
        $name,
        \Eloquent\Phony\Call\Argument\ArgumentsInterface $arguments
    ) {
        return $this->_proxy
            ->spy('__call')->invoke($name, $arguments->all());
    }

EOD;
        }

        return $source;
    }

    /**
     * Generate the properties.
     *
     * @param MockDefinitionInterface $definition The definition.
     *
     * @return string The source code.
     */
    protected function generateProperties(MockDefinitionInterface $definition)
    {
        $staticProperties = $definition->customStaticProperties();
        $properties = $definition->customProperties();
        $source = '';

        foreach ($staticProperties as $name => $value) {
            $source .=
                "\n    public static \$" .
                $name .
                ' = ' .
                (null === $value ? 'null' : $this->renderValue($value)) .
                ';';
        }

        foreach ($properties as $name => $value) {
            $source .=
                "\n    public \$" .
                $name .
                ' = ' .
                (null === $value ? 'null' : $this->renderValue($value)) .
                ';';
        }

        $methods = $definition->methods()->allMethods();
        $uncallableMethodNames = array();
        $traitMethodNames = array();

        foreach ($methods as $methodName => $method) {
            $methodName = strtolower($methodName);

            if (!$method->isCallable()) {
                $uncallableMethodNames[$methodName] = true;
            } elseif ($method instanceof TraitMethodDefinitionInterface) {
                $traitMethodNames[$methodName] =
                    $method->method()->getDeclaringClass()->getName();
            }
        }

        $source .= "\n    private static \$_uncallableMethods = ";

        if ($uncallableMethodNames) {
            $source .= $this->renderValue($uncallableMethodNames);
        } else {
            $source .= 'array()';
        }

        $source .= ";\n    private static \$_traitMethods = ";

        if ($traitMethodNames) {
            $source .= $this->renderValue($traitMethodNames);
        } else {
            $source .= 'array()';
        }

        $source .= ";\n    private static \$_customMethods = array();" .
            "\n    private static \$_staticProxy;" .
            "\n    private \$_proxy;";

        return $source;
    }

    /**
     * Render the supplied value.
     *
     * @param mixed $value The value.
     *
     * @return string The rendered value.
     */
    protected function renderValue($value)
    {
        return str_replace('array (', 'array(', var_export($value, true));
    }

    private static $instance;
    private $labelSequencer;
    private $signatureInspector;
    private $featureDetector;
    private $isClosureBindingSupported;
}
