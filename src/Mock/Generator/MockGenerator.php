<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Mock\Generator;

use Eloquent\Phony\Feature\FeatureDetector;
use Eloquent\Phony\Feature\FeatureDetectorInterface;
use Eloquent\Phony\Mock\Builder\Definition\Method\MethodDefinitionInterface;
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
     * @param SequencerInterface|null                  $idSequencer        The identifier sequencer to use.
     * @param FunctionSignatureInspectorInterface|null $signatureInspector The function signature inspector to use.
     * @param FeatureDetectorInterface|null            $featureDetector    The feature detector to use.
     */
    public function __construct(
        SequencerInterface $idSequencer = null,
        FunctionSignatureInspectorInterface $signatureInspector = null,
        FeatureDetectorInterface $featureDetector = null
    ) {
        if (null === $idSequencer) {
            $idSequencer = Sequencer::sequence('mock-class-id');
        }
        if (null === $signatureInspector) {
            $signatureInspector = FunctionSignatureInspector::instance();
        }
        if (null === $featureDetector) {
            $featureDetector = FeatureDetector::instance();
        }

        $this->idSequencer = $idSequencer;
        $this->signatureInspector = $signatureInspector;
        $this->featureDetector = $featureDetector;
    }

    /**
     * Get the identifier sequencer.
     *
     * @return SequencerInterface The identifier sequencer.
     */
    public function idSequencer()
    {
        return $this->idSequencer;
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

        $className .= '_' . $this->idSequencer->next();

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

            foreach ($methods->traitResolutions() as $resolution) {
                $source .= "\n        \\" .
                    $resolution[0] .
                    '::' .
                    $resolution[1] .
                    "\n            insteadof \\" .
                    $resolution[2] .
                    ';';
            }

            foreach ($methods->traitMethods() as $methodName => $method) {
                $source .= "\n        \\" .
                    $method->getDeclaringClass()->getName() .
                    '::' .
                    $methodName .
                    "\n            as private _callTrait_" .
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
                    (null === $value ? 'null' : var_export($value, true)) .
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
    protected function generateMagicCallStatic(MockDefinitionInterface $definition)
    {
        $methods = $definition->methods()->publicStaticMethods();

        if (!isset($methods['__callStatic'])) {
            return '';
        }

        return <<<'EOD'

    public static function __callStatic(
        $a0,
        array $a1
    ) {
        return self::$_staticProxy->spy($a0)
            ->invokeWith(new \Eloquent\Phony\Call\Argument\Arguments($a1));
    }

EOD;
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
        $className = $definition->parentClassName();

        if (null === $className) {
            $constructor = null;
        } else {
            $types = $definition->types();
            $constructor = $types[$className]->getConstructor();
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
     * Generate the supplied methods
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

            if ('__call' === $name || '__callStatic' === $name) {
                continue;
            }

            $signature =
                $this->signatureInspector->signature($method->method());

            if ($method->isCustom()) {
                array_shift($signature);
            }

            $parameterCount = count($signature);

            if ($signature) {
                $argumentPacking = "\n";
                $index = -1;

                foreach ($signature as $parameter) {
                    $argumentPacking .= "\n        if (\$argumentCount > " .
                        ++$index .
                        ') $arguments[] = ' .
                        $parameter[1] .
                        '$a' .
                        $index .
                        ';';
                }
            } else {
                $argumentPacking = '';
            }

            $isStatic = $method->isStatic();

            if ($isStatic) {
                $body = "        \$argumentCount = func_num_args();\n" .
                    "        \$arguments = array();" .
                    $argumentPacking .
                    "\n\n        for (\$i = " .
                    $parameterCount .
                    "; \$i < \$argumentCount; \$i++) {\n" .
                    "            \$arguments[] = func_get_arg(\$i);\n" .
                    "        }\n\n        return self::\$_staticProxy->spy" .
                    "(__FUNCTION__)->invokeWith(\n            " .
                    "new \Eloquent\Phony\Call\Argument\Arguments" .
                    "(\$arguments)\n        );";
            } else {
                $body = "        \$argumentCount = func_num_args();\n" .
                    "        \$arguments = array();" .
                    $argumentPacking .
                    "\n\n        for (\$i = " .
                    $parameterCount .
                    "; \$i < \$argumentCount; \$i++) {\n" .
                    "            \$arguments[] = func_get_arg(\$i);\n" .
                    "        }\n\n        return \$this->_proxy->spy" .
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
                        '$a' .
                        ++$index .
                        $parameter[2];
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
        $methods = $definition->methods()->publicMethods();

        if (!isset($methods['__call'])) {
            return '';
        }

        return <<<'EOD'

    public function __call(
        $a0,
        array $a1
    ) {
        return $this->_proxy->spy($a0)
            ->invokeWith(new \Eloquent\Phony\Call\Argument\Arguments($a1));
    }

EOD;
    }

    /**
     * Generate the call parent methods.
     *
     * @param MockDefinitionInterface $definition The definition.
     *
     * @return string The source code.
     */
    protected function generateCallParentMethods(MockDefinitionInterface $definition)
    {
        $hasParentClass = null !== $definition->parentClassName();
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

        $methods = $definition->methods()->publicStaticMethods();

        if (isset($methods['__callStatic'])) {
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
        }

        $methods = $definition->methods()->publicMethods();

        if (isset($methods['__call'])) {
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
                (null === $value ? 'null' : var_export($value, true)) .
                ';';
        }

        foreach ($properties as $name => $value) {
            $source .=
                "\n    public \$" .
                $name .
                ' = ' .
                (null === $value ? 'null' : var_export($value, true)) .
                ';';
        }

        $source .= <<<'EOD'

    private static $_customMethods = array();
    private static $_staticProxy;
    private $_proxy;
EOD;

        return $source;
    }

    private static $instance;
    private $idSequencer;
    private $signatureInspector;
    private $featureDetector;
}
