<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2016 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Stub;

use Eloquent\Phony\Reflection\FeatureDetector;

/**
 * Generates the source code for function hooks.
 */
class FunctionHookGenerator
{
    /**
     * Get the static instance of this generator.
     *
     * @return FunctionHookGenerator The static generator.
     */
    public static function instance()
    {
        if (!self::$instance) {
            self::$instance = new self(FeatureDetector::instance());
        }

        return self::$instance;
    }

    /**
     * Construct a new function hook manager.
     *
     * @param FeatureDetector $featureDetector The feature detector to use.
     */
    public function __construct(FeatureDetector $featureDetector)
    {
        $this->isEngineErrorExceptionSupported =
            $featureDetector->isSupported('error.exception.engine');
    }

    /**
     * Generate the source code for a function hook.
     *
     * @param string                      $name      The function name.
     * @param array<string,array<string>> $signature The function signature.
     *
     * @return string The source code.
     */
    public function generateHook($name, array $signature)
    {
        $atoms = explode('\\', $name);
        $shortName = array_pop($atoms);

        if ($atoms) {
            $namespace = implode('\\', $atoms);
            $source = "namespace $namespace;\n\n";
        } else {
            $source = '';
        }

        $source .= "function $shortName";

        if ($signature) {
            $index = -1;
            $isFirst = true;

            foreach ($signature as $parameter) {
                if ($isFirst) {
                    $isFirst = false;
                    $source .= "(\n    ";
                } else {
                    $source .= ",\n    ";
                }

                $source .= $parameter[0] .
                    $parameter[1] .
                    $parameter[2] .
                    '$a' .
                    ++$index .
                    $parameter[3];
            }

            $source .= "\n) {\n";
        } else {
            $source .= "()\n{\n";
        }

        $ret = 'ret' . 'urn';
        $thr = 'thr' . 'ow';

        $renderedName = var_export($name, true);
        $source .=
            "    \$name = $renderedName;\n\n    if (" .
            "\n        !isset(\n            " .
            '\Eloquent\Phony\Stub\FunctionHookManager::$hooks[$name]' .
            "['callback']\n        )\n    ) {\n";

        if ($this->isEngineErrorExceptionSupported) {
            $source .= '        ' .
                "$thr new \Error('Call to undefined function $name()');";
            // @codeCoverageIgnoreStart
        } else {
            $source .= '        trigger_error(' .
                "'Call to undefined function $name()', E_USER_ERROR);\n\n" .
                "    $ret;";
        }
        // @codeCoverageIgnoreEnd

        $source .= "\n    }\n\n";

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
                    $argumentPacking .=
                        "\n    if (\$argumentCount > " .
                        ++$index .
                        ") {\n        \$arguments[] = " .
                        $parameter[1] .
                        '$a' .
                        $index .
                        ";\n    }";
                }
            }
        } else {
            $argumentPacking = '';
        }

        $source .=
            "    \$argumentCount = \\func_num_args();\n" .
            '    $arguments = array();' .
            $argumentPacking .
            "\n\n    for (\$i = " .
            $parameterCount .
            "; \$i < \$argumentCount; ++\$i) {\n";

        if ($variadicIndex > -1) {
            $source .=
                "        \$arguments[] = $variadicReference\$a" .
                $variadicIndex . "[\$i - $variadicIndex];\n" .
                '    }';
        } else {
            $source .=
                "        \$arguments[] = \\func_get_arg(\$i);\n" .
                '    }';
        }

        $source .=
            "\n\n    \$callback =\n        " .
            '\Eloquent\Phony\Stub\FunctionHookManager::$hooks' .
            "[\$name]['callback'];\n\n" .
            '    if ($callback instanceof ' .
            "\Eloquent\Phony\Invocation\Invocable) {\n" .
            "        $ret \$callback->invokeWith(\$arguments);\n" .
            "    }\n\n    " .
            "$ret \\call_user_func_array(\$callback, \$arguments);\n}\n";

        // @codeCoverageIgnoreStart
        if ("\n" !== PHP_EOL) {
            $source = str_replace("\n", PHP_EOL, $source);
        }
        // @codeCoverageIgnoreEnd

        return $source;
    }

    private static $instance;
    private $isEngineErrorExceptionSupported;
}
