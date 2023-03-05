<?php

declare(strict_types=1);

namespace Eloquent\Phony\Hook;

/**
 * Generates the source code for function hooks.
 */
class FunctionHookGenerator
{
    /**
     * Generate the source code for a function hook.
     *
     * @param string $name      The function name.
     * @param string $namespace The namespace.
     * @param array{array<string,array<int,string>>,string} $signature The function signature.
     *
     * @return string The source code.
     */
    public function generateHook(
        string $name,
        string $namespace,
        array $signature
    ): string {
        $arguments = self::VAR_PREFIX . 'arguments';
        $argumentCount = self::VAR_PREFIX . 'argumentCount';
        $i = self::VAR_PREFIX . 'i';
        $nameVar = self::VAR_PREFIX . 'name';
        $callback = self::VAR_PREFIX . 'callback';

        $source = "namespace $namespace;\n\nfunction $name";
        list($parameters) = $signature;
        $parameterCount = count($parameters);

        if ($parameterCount > 0) {
            $isFirst = true;

            foreach ($parameters as $parameterName => $parameter) {
                if ($isFirst) {
                    $isFirst = false;
                    $source .= "(\n    ";
                } else {
                    $source .= ",\n    ";
                }

                $source .= $parameter[0] .
                    $parameter[1] .
                    $parameter[2] .
                    '$' . $parameterName .
                    $parameter[3];
            }

            $source .= "\n) {\n";
        } else {
            $source .= "()\n{\n";
        }

        $variadicIndex = -1;
        $variadicReference = '';
        $variadicName = '';

        if ($parameterCount > 0) {
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
                        "\n    if ($argumentCount > " .
                        ++$index .
                        ") {\n        {$arguments}[] = " .
                        $parameter[1] .
                        '$' . $parameterName .
                        ";\n    }";
                }
            }
        } else {
            $argumentPacking = '';
        }

        $source .=
            "    $argumentCount = \\func_num_args();\n" .
            "    $arguments = [];" .
            $argumentPacking .
            "\n\n    for ($i = " .
            $parameterCount .
            "; $i < $argumentCount; ++$i) {\n";

        if ($variadicIndex > -1) {
            $source .=
                "        {$arguments}[] = $variadicReference\$" .
                "{$variadicName}[$i - $variadicIndex];\n" .
                '    }';
        } else {
            $source .=
                "        {$arguments}[] = \\func_get_arg($i);\n" .
                '    }';
        }

        $ret = 'ret' . 'urn';

        $renderedName = var_export(strtolower($namespace . '\\' . $name), true);
        $source .=
            "\n\n    $nameVar = $renderedName;\n\n    if (" .
            "\n        !isset(\n            " .
            '\Eloquent\Phony\Hook\FunctionHookManager::$hooks' . "[$nameVar]" .
            "['callback']\n        )\n    ) {\n        " .
            "$ret \\$name(...$arguments);" .
            "\n    }\n\n    $callback =\n        " .
            '\Eloquent\Phony\Hook\FunctionHookManager::$hooks' .
            "[$nameVar]['callback'];\n\n" .
            "    if ($callback instanceof " .
            "\Eloquent\Phony\Invocation\Invocable) {\n" .
            "        $ret {$callback}->invokeWith($arguments);\n" .
            "    }\n\n    " .
            "$ret $callback(...$arguments);\n}\n";

        // @codeCoverageIgnoreStart
        if ("\n" !== PHP_EOL) {
            $source = str_replace("\n", PHP_EOL, $source);
        }
        // @codeCoverageIgnoreEnd

        return $source;
    }

    const VAR_PREFIX = "$\u{a4}";
}
