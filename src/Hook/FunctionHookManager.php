<?php

declare(strict_types=1);

namespace Eloquent\Phony\Hook;

use Eloquent\Phony\Hook\Exception\FunctionExistsException;
use Eloquent\Phony\Hook\Exception\FunctionHookException;
use Eloquent\Phony\Hook\Exception\FunctionHookGenerationFailedException;
use Eloquent\Phony\Hook\Exception\FunctionSignatureMismatchException;
use Eloquent\Phony\Invocation\InvocableInspector;
use Eloquent\Phony\Reflection\FunctionSignatureInspector;
use ParseError;

/**
 * Allows control over the behavior of function hooks.
 */
class FunctionHookManager
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
                InvocableInspector::instance(),
                FunctionSignatureInspector::instance(),
                FunctionHookGenerator::instance()
            );
        }

        return self::$instance;
    }

    /**
     * Construct a new function hook manager.
     *
     * @param InvocableInspector         $invocableInspector The invocable inspector to use.
     * @param FunctionSignatureInspector $signatureInspector The function signature inspector to use.
     * @param FunctionHookGenerator      $hookGenerator      The function hook generator to use.
     */
    public function __construct(
        InvocableInspector $invocableInspector,
        FunctionSignatureInspector $signatureInspector,
        FunctionHookGenerator $hookGenerator
    ) {
        $this->invocableInspector = $invocableInspector;
        $this->signatureInspector = $signatureInspector;
        $this->hookGenerator = $hookGenerator;
    }

    /**
     * Define the behavior of a function hook.
     *
     * @param string   $name      The function name.
     * @param string   $namespace The namespace.
     * @param callable $callback  The callback.
     *
     * @return ?callable             The replaced callback, or null if no callback was set.
     * @throws FunctionHookException If the function hook generation fails.
     */
    public function defineFunction(
        string $name,
        string $namespace,
        callable $callback
    ): ?callable {
        $signature = $this->signatureInspector->signature(
            $this->invocableInspector->callbackReflector($callback)
        );
        $fullName = $namespace . '\\' . $name;
        $key = strtolower($fullName);

        if (isset(self::$hooks[$key])) {
            if ($signature !== self::$hooks[$key]['signature']) {
                throw new FunctionSignatureMismatchException($fullName);
            }

            $replaced = self::$hooks[$key]['callback'];
        } else {
            $replaced = null;

            if (function_exists($fullName)) {
                throw new FunctionExistsException($fullName);
            }

            $source = $this->hookGenerator
                ->generateHook($name, $namespace, $signature);
            $reporting = error_reporting(E_ERROR | E_COMPILE_ERROR);

            try {
                eval($source);
            } catch (ParseError $e) {
                throw new FunctionHookGenerationFailedException(
                    $fullName,
                    $callback,
                    $source,
                    error_get_last(),
                    $e
                );
            } finally {
                error_reporting($reporting);
            }

            if (!function_exists($fullName)) {
                // @codeCoverageIgnoreStart
                throw new FunctionHookGenerationFailedException(
                    $fullName,
                    $callback,
                    $source,
                    error_get_last()
                );
                // @codeCoverageIgnoreEnd
            }
        }

        self::$hooks[$key] =
            ['callback' => $callback, 'signature' => $signature];

        return $replaced;
    }

    /**
     * Effectively removes any function hooks for functions in the global
     * namespace.
     */
    public function restoreGlobalFunctions(): void
    {
        foreach (self::$hooks as $key => $data) {
            self::$hooks[$key]['callback'] = null;
        }
    }

    /**
     * @var array<string,array<string,mixed>>
     */
    public static $hooks = [];

    /**
     * @var ?self
     */
    private static $instance;

    /**
     * @var InvocableInspector
     */
    private $invocableInspector;

    /**
     * @var FunctionSignatureInspector
     */
    private $signatureInspector;

    /**
     * @var FunctionHookGenerator
     */
    private $hookGenerator;
}
