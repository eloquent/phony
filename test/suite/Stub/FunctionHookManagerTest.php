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
use Eloquent\Phony\Test\TestFunctionHookGenerator;
use Error;
use PHPUnit_Framework_TestCase;
use ReflectionClass;

class FunctionHookManagerTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->functionSignatureInspector = FunctionSignatureInspector::instance();
        $this->hookGenerator = FunctionHookGenerator::instance();
        $this->subject = new FunctionHookManager($this->functionSignatureInspector, $this->hookGenerator);

        $this->name = 'Eloquent\Phony\Test\\phony_' . md5(mt_rand());
    }

    public function testDefineFunction()
    {
        $name = $this->name;
        $callbackA = function () {
            return implode(', ', func_get_args());
        };
        $callbackB = function () {};

        $this->assertFalse(function_exists($name));

        $this->subject->defineFunction($name, $callbackA);

        $this->assertTrue(function_exists($name));
        $this->assertSame('a, b, c', $name('a', 'b', 'c'));
        $this->assertSame($callbackA, $this->subject->defineFunction($name, $callbackB));
        $this->assertNull($name('a', 'b', 'c'));
    }

    public function testDefineFunctionWithoutNamespace()
    {
        $this->name = $name = 'phony_' . md5(mt_rand());
        $callbackA = function () {
            return implode(', ', func_get_args());
        };
        $callbackB = function () {};

        $this->assertFalse(function_exists($name));

        $this->subject->defineFunction($name, $callbackA);

        $this->assertTrue(function_exists($name));
        $this->assertSame('a, b, c', $name('a', 'b', 'c'));
        $this->assertSame($callbackA, $this->subject->defineFunction($name, $callbackB));
        $this->assertNull($name('a', 'b', 'c'));
    }

    public function testDefineFunctionWithReferenceParameters()
    {
        $name = $this->name;
        $callback = function (&$a, &$b) {
            $a = 'a';
            $b = 'b';
        };

        $this->subject->defineFunction($name, $callback);

        $a = null;
        $b = null;
        $name($a, $b);

        $this->assertSame('a', $a);
        $this->assertSame('b', $b);
    }

    public function testDefineFunctionFailureSignatureMismatch()
    {
        $this->subject->defineFunction($this->name, function ($a = null) {});

        $this->setExpectedException('Eloquent\Phony\Stub\Exception\FunctionSignatureMismatchException');
        $this->subject->defineFunction($this->name, function () {});
    }

    public function testDefineFunctionFailureExistantFunction()
    {
        $this->setExpectedException('Eloquent\Phony\Stub\Exception\FunctionExistsException');
        $this->subject->defineFunction('implode', function () {});
    }

    public function testDefineFunctionFailureSyntax()
    {
        $this->hookGenerator = new TestFunctionHookGenerator('{');
        $this->subject = new FunctionHookManager($this->functionSignatureInspector, $this->hookGenerator);

        $this->setExpectedException('Eloquent\Phony\Stub\Exception\FunctionHookGenerationFailedException');
        $this->subject->defineFunction($this->name, function () {});
    }

    public function testUndefineFunction()
    {
        $name = $this->name;
        $callback = function () {};

        $this->assertFalse(function_exists($name));
        $this->assertNull($this->subject->undefineFunction($name));

        $this->subject->defineFunction($name, $callback);

        $this->assertSame($callback, $this->subject->undefineFunction($name));

        $actual = null;

        set_error_handler(
            function ($severity, $message) use (&$actual) {
                $actual = $message;
            }
        );

        try {
            $name();
        } catch (Error $error) {
            $actual = $error->getMessage();
        }

        $this->assertSame("Call to undefined function $name()", $actual);

        restore_error_handler();
    }

    public function testInstance()
    {
        $class = get_class($this->subject);
        $reflector = new ReflectionClass($class);
        $property = $reflector->getProperty('instance');
        $property->setAccessible(true);
        $property->setValue(null, null);
        $instance = $class::instance();

        $this->assertInstanceOf($class, $instance);
        $this->assertSame($instance, $class::instance());
    }
}
