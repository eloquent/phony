<?php

declare(strict_types=1);

namespace Eloquent\Phony\Hook;

use AllowDynamicProperties;
use Eloquent\Phony\Hook\Exception\FunctionExistsException;
use Eloquent\Phony\Hook\Exception\FunctionHookGenerationFailedException;
use Eloquent\Phony\Hook\Exception\FunctionSignatureMismatchException;
use Eloquent\Phony\Test\Facade\FacadeContainer;
use Eloquent\Phony\Test\FunctionHookManager as TestNamespace;
use Eloquent\Phony\Test\TestFunctionHookGenerator;
use PHPUnit\Framework\TestCase;

#[AllowDynamicProperties]
class FunctionHookManagerTest extends TestCase
{
    protected function setUp(): void
    {
        $this->container = new FacadeContainer();
        $this->subject = $this->container->functionHookManager;

        $this->namespace = TestNamespace::class;
        $this->name = 'phony_' . md5(strval(mt_rand()));
        $this->fullName = $this->namespace . '\\' . $this->name;
    }

    public function testDefineFunction()
    {
        $callbackA = function () {
            return implode(', ', func_get_args());
        };
        $callbackB = function () {};

        $this->assertFalse(function_exists($this->fullName));

        $this->subject->defineFunction($this->name, $this->namespace, $callbackA);

        $this->assertTrue(function_exists($this->fullName));
        $this->assertSame('a, b, c', call_user_func($this->fullName, 'a', 'b', 'c'));
        $this->assertSame($callbackA, $this->subject->defineFunction($this->name, $this->namespace, $callbackB));
        $this->assertNull(call_user_func($this->fullName, 'a', 'b', 'c'));
    }

    public function testDefineFunctionWithReferenceParameters()
    {
        $callback = function (&$a, &$b) {
            $a = 'a';
            $b = 'b';
        };

        $this->subject->defineFunction($this->name, $this->namespace, $callback);

        $a = null;
        $b = null;
        call_user_func_array($this->fullName, [&$a, &$b]);

        $this->assertSame('a', $a);
        $this->assertSame('b', $b);
    }

    public function testDefineFunctionFailureSignatureMismatch()
    {
        $this->subject->defineFunction($this->name, $this->namespace, function ($a = null) {});

        $this->expectException(FunctionSignatureMismatchException::class);
        $this->subject->defineFunction($this->name, $this->namespace, function () {});
    }

    public function testDefineFunctionFailureExistantFunction()
    {
        if (!function_exists($this->namespace . '\\existant')) {
            eval("namespace $this->namespace;\nfunction existant () {}");
        }

        $this->expectException(FunctionExistsException::class);
        $this->subject->defineFunction('existant', $this->namespace, function () {});
    }

    public function testDefineFunctionFailureSyntax()
    {
        $hookGenerator = new TestFunctionHookGenerator('{');
        $this->subject = new FunctionHookManager(
            $this->container->invocableInspector,
            $this->container->functionSignatureInspector,
            $hookGenerator
        );

        $this->expectException(FunctionHookGenerationFailedException::class);
        $this->subject->defineFunction($this->name, $this->namespace, function () {});
    }

    public function testRestoreGlobalFunctions()
    {
        $this->subject->defineFunction(
            'sprintf',
            $this->namespace,
            function ($pattern) {
                return 'x';
            }
        );
        $this->subject->defineFunction(
            'vsprintf',
            $this->namespace,
            function ($pattern) {
                return 'y';
            }
        );

        $this->assertSame('x', call_user_func($this->namespace . '\\sprintf', '%s', 'a'));
        $this->assertSame('y', call_user_func($this->namespace . '\\vsprintf', '%s', ['b']));

        $this->subject->restoreGlobalFunctions();

        $this->assertSame('a', call_user_func($this->namespace . '\\sprintf', '%s', 'a'));
        $this->assertSame('b', call_user_func($this->namespace . '\\vsprintf', '%s', ['b']));
    }
}
