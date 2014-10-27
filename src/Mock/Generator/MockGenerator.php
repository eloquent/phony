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

        $source = $namespace .
            "/**\n * A mock class generated by Phony." .
            $usedTypes .
            <<<'EOD'

 *
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with the Phony source code.
 *
 * @link https://github.com/eloquent/phony
 */
class
EOD
            . ' ' .
            $className;


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
            foreach ($traitNames as $traitName) {
                $source .= "\n    use \\" . $traitName . ';';
            }

            $source .= "\n";
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
                    $this->renderValue($value) .
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

        $body = "        return self::\$_staticProxy->spy(\$a0)\n" .
            "            ->invokeWith(new " .
            "\Eloquent\Phony\Call\Argument\Arguments(\$a1));";

        return $this->generateMethod(
            $methods['__callStatic'],
            $this->signatureInspector
                ->signature($methods['__callStatic']->method()),
            $body
        );
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

    /**
     * Construct a mock.
     */
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
            if (
                '__call' === $method->name() ||
                '__callStatic' === $method->name()
            ) {
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

                foreach ($signature as $name => $parameter) {
                    $argumentPacking .= "\n        if (\$argumentCount > " .
                        ++$index .
                        ') $arguments[] = ' .
                        $parameter[2] .
                        '$a' .
                        $index .
                        ';';
                }
            } else {
                $argumentPacking = '';
            }

            if ($method->isStatic()) {
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

            $source .= $this->generateMethod($method, $signature, $body);
        }

        return $source;
    }

    /**
     * Generate the supplied method.
     *
     * @param MethodDefinitionInterface           $method    The method.
     * @param array<string,array<integer,string>> $signature The signature.
     * @param string                              $body      The method body.
     *
     * @return string The source code.
     */
    protected function generateMethod(
        MethodDefinitionInterface $method,
        array $signature,
        $body
    ) {
        $source = '';

        $name = $method->name();

        if ($signature) {
            $parametersDocumentation =
                $this->renderParametersDocumentation($signature);
        } else {
            $parametersDocumentation = '';
        }

        if ($method->isCustom()) {
            $comment = "    /**\n     * Custom method '" .
                $name .
                "'." .
                $parametersDocumentation .
                "\n     */";
        } else {
            $comment = "    /**\n     * Inherited method '" .
                $name .
                "'.\n     *\n     * @uses \\" .
                $method->method()->getDeclaringClass()->getName() .
                '::' .
                $name .
                '()' .
                $parametersDocumentation .
                "\n     */";
        }

        if ($method->isStatic()) {
            $scope = 'static ';
        } else {
            $scope = '';
        }

        return "\n" .
            $comment .
            "\n    " .
            $method->accessLevel() .
            ' ' .
            $scope .
            'function '.
            $method->name() .
            $this->renderParameters($signature) .
            $body .
            "\n    }\n";
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

        return $this->generateMethod(
            $methods['__call'],
            $this->signatureInspector->signature($methods['__call']->method()),
            "        return \$this->_proxy->spy(\$a0)\n            ->" .
                "invokeWith(new \Eloquent\Phony\Call\Argument\Arguments(\$a1));"
        );
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

    /**
     * Call a static parent method.
     *
     * @param string                                           $name      The method name.
     * @param \Eloquent\Phony\Call\Argument\ArgumentsInterface $arguments The arguments.
     */
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

    /**
     * Perform a magic call via the __callStatic stub.
     *
     * @param string                                           $name      The method name.
     * @param \Eloquent\Phony\Call\Argument\ArgumentsInterface $arguments The arguments.
     */
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

    /**
     * Call a parent method.
     *
     * @param string                                           $name      The method name.
     * @param \Eloquent\Phony\Call\Argument\ArgumentsInterface $arguments The arguments.
     */
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

    /**
     * Perform a magic call via the __call stub.
     *
     * @param string                                           $name      The method name.
     * @param \Eloquent\Phony\Call\Argument\ArgumentsInterface $arguments The arguments.
     */
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
                $this->renderValue($value) .
                ';';
        }

        foreach ($properties as $name => $value) {
            $source .=
                "\n    public \$" .
                $name .
                ' = ' .
                $this->renderValue($value) .
                ';';
        }

        $source .= <<<'EOD'

    private static $_customMethods = array();
    private static $_staticProxy;
    private $_proxy;
EOD;

        return $source;
    }

    /**
     * Render a parameter list compatible with the supplied function reflector.
     *
     * @param array<string,array<integer,string>> $signature The signature.
     *
     * @return string The rendered parameter list.
     */
    protected function renderParameters(array $signature)
    {
        if (!$signature) {
            return "()\n    {\n";
        }

        $renderedParameters = array();
        $index = -1;

        foreach ($signature as $parameter) {
            $renderedParameters[] =
                $parameter[0] . $parameter[2] . '$a' . ++$index . $parameter[3];
        }

        return "(\n        " .
            implode(",\n        ", $renderedParameters) .
            "\n    ) {\n";
    }

    /**
     * Render parameter documentation for a function reflector.
     *
     * @param array<string,array<integer,string>> $signature The signature.
     *
     * @return string The rendered documentation.
     */
    protected function renderParametersDocumentation(array $signature)
    {
        $renderedParameters = array();
        $index = -1;
        $maxArgumentNameSize = 0;

        foreach ($signature as $name => $parameter) {
            $argumentName = $parameter[2] . '$a' . ++$index;
            $renderedParameters[] = array(
                "\n     * @param " . $parameter[1],
                $argumentName,
                " Was '" . $name . "'.",
            );

            $argumentNameSize = strlen($argumentName);

            if ($argumentNameSize > $maxArgumentNameSize) {
                $maxArgumentNameSize = $argumentNameSize;
            }
        }

        $rendered = "\n     *";

        foreach ($renderedParameters as $renderedParameter) {
            $rendered .= $renderedParameter[0] .
                str_pad($renderedParameter[1], $maxArgumentNameSize, ' ') .
                $renderedParameter[2];
        }

        return $rendered;
    }

    /**
     * Render the supplied value.
     *
     * This method does not support recursive values, which will result in an
     * infinite loop.
     *
     * @param mixed $value The value.
     *
     * @return string The rendered value.
     */
    protected function renderValue($value)
    {
        if (null === $value) {
            return 'null';
        }

        if (is_array($value)) {
            $values = array();

            foreach ($value as $key => $subValue) {
                $values[] = var_export($key, true) .
                    ' => ' .
                    $this->renderValue($subValue);
            }

            return 'array(' . implode(', ', $values) . ')';
        }

        return var_export($value, true);
    }

    private static $instance;
    private $idSequencer;
    private $signatureInspector;
    private $featureDetector;
}
