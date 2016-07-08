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

use Eloquent\Phony\Reflection\FunctionSignatureInspector;
use Eloquent\Phony\Stub\Exception\FunctionExistsException;
use Eloquent\Phony\Stub\Exception\FunctionHookException;
use Eloquent\Phony\Stub\Exception\FunctionHookGenerationFailedException;
use Eloquent\Phony\Stub\Exception\FunctionSignatureMismatchException;
use Exception;
use ParseError;
use ParseException;
use Throwable;

/**
 * Allows control over the behavior of function hooks.
 */
class FunctionHookManager
{
    /**
     * Get the static instance of this manager.
     *
     * @return FunctionHookManager The static manager.
     */
    public static function instance()
    {
        if (!self::$instance) {
            self::$instance = new self(
                FunctionSignatureInspector::instance(),
                FunctionHookGenerator::instance()
            );
        }

        return self::$instance;
    }

    /**
     * Construct a new function hook manager.
     *
     * @param FunctionSignatureInspector $signatureInspector The function signature inspector to use.
     * @param FunctionHookGenerator      $hookGenerator      The function hook generator to use.
     */
    public function __construct(
        FunctionSignatureInspector $signatureInspector,
        FunctionHookGenerator $hookGenerator
    ) {
        $this->signatureInspector = $signatureInspector;
        $this->hookGenerator = $hookGenerator;
    }

    /**
     * Define the behavior of a function hook.
     *
     * @param string   $name     The function name.
     * @param callback $callback The callback.
     *
     * @return callback|null         The replaced callback, or null if no callback was set.
     * @throws FunctionHookException If the function hook generation fails.
     */
    public function defineFunction($name, $callback)
    {
        $signature = $this->signatureInspector->callbackSignature($callback);

        if (isset(self::$hooks[$name])) {
            if ($signature !== self::$hooks[$name]['signature']) {
                throw new FunctionSignatureMismatchException($name);
            }

            $replaced = self::$hooks[$name]['callback'];
        } else {
            $replaced = null;

            if (function_exists($name)) {
                throw new FunctionExistsException($name);
            }

            $source = $this->hookGenerator->generateHook($name, $signature);
            $reporting = error_reporting(E_ERROR | E_COMPILE_ERROR);
            $error = null;

            try {
                eval($source);
            } catch (ParseError $e) {
                $error = new FunctionHookGenerationFailedException(
                    $name,
                    $callback,
                    $source,
                    error_get_last(),
                    $e
                );
                // @codeCoverageIgnoreStart
            } catch (ParseException $e) {
                $error = new FunctionHookGenerationFailedException(
                    $name,
                    $callback,
                    $source,
                    error_get_last(),
                    $e
                );
            } catch (Throwable $error) {
            } catch (Exception $error) {
            }
            // @codeCoverageIgnoreEnd

            error_reporting($reporting);

            if ($error) {
                throw $error;
            }

            if (!function_exists($name)) {
                // @codeCoverageIgnoreStart
                throw new FunctionHookGenerationFailedException(
                    $name,
                    $callback,
                    $source,
                    error_get_last()
                );
                // @codeCoverageIgnoreEnd
            }
        }

        self::$hooks[$name] =
            array('callback' => $callback, 'signature' => $signature);

        return $replaced;
    }

    /**
     * Remove any existing behavior from a function hook.
     *
     * @param string $name The function name.
     *
     * @return callback|null The removed callback, or null if no callback was set.
     */
    public function undefineFunction($name)
    {
        if (isset(self::$hooks[$name])) {
            $removed = self::$hooks[$name]['callback'];
            self::$hooks[$name]['callback'] = null;
        } else {
            $removed = null;
        }

        return $removed;
    }

    public static $hooks = array();
    private static $instance;
    private $signatureInspector;
    private $hookGenerator;
}
