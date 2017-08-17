<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2017 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

use Eloquent\Phony as x;
use Eloquent\Phony\Reflection\FeatureDetector;
use PHPUnit\Framework\TestCase;

class EdgeCaseTest extends TestCase
{
    protected function setUp()
    {
        $this->featureDetector = FeatureDetector::instance();
    }

    public function testSimpleXmlElementFull()
    {
        if (!class_exists('SimpleXMLElement')) {
            $this->markTestSkipped('Requires the SimpleXMLElement class.');
        }
        if (!$this->featureDetector->isSupported('object.constructor.bypass.extended-internal')) {
            $this->markTestSkipped('Requires support for bypassing constructors of classes that extend internals');
        }

        $full = x\mock('SimpleXMLElement');
        $mock = $full->get();

        $this->assertTrue($mock instanceof SimpleXMLElement);
    }

    public function testSimpleXmlElementPartial()
    {
        if (!class_exists('SimpleXMLElement')) {
            $this->markTestSkipped('Requires the SimpleXMLElement class.');
        }

        $partial = x\partialMock('SimpleXMLElement', array('<root><sub></sub></root>'));
        $mock = $partial->get();

        $this->assertTrue($mock instanceof SimpleXMLElement);
        $this->assertTrue($mock->sub instanceof SimpleXMLElement);
    }

    public function testSimpleXmlIteratorFull()
    {
        if (!class_exists('SimpleXMLIterator')) {
            $this->markTestSkipped('Requires the SimpleXMLIterator class.');
        }
        if (!$this->featureDetector->isSupported('object.constructor.bypass.extended-internal')) {
            $this->markTestSkipped('Requires support for bypassing constructors of classes that extend internals');
        }

        $full = x\mock('SimpleXMLIterator');
        $mock = $full->get();

        $this->assertTrue($mock instanceof SimpleXMLIterator);
    }

    public function testSimpleXmlIteratorPartial()
    {
        if (!class_exists('SimpleXMLIterator')) {
            $this->markTestSkipped('Requires the SimpleXMLIterator class.');
        }

        $partial = x\partialMock('SimpleXMLIterator', array('<root><sub></sub></root>'));
        $mock = $partial->get();

        $this->assertTrue($mock instanceof SimpleXMLIterator);
        $this->assertTrue($mock->sub instanceof SimpleXMLIterator);
    }

    public function typeData()
    {
        $data = array();

        if (function_exists('get_declared_traits')) {
            $typeNames = array_merge(get_declared_classes(), get_declared_interfaces(), get_declared_traits());
        } else {
            $typeNames = array_merge(get_declared_classes(), get_declared_interfaces());
        }

        $isHhvm = FeatureDetector::instance()->isSupported('runtime.hhvm');

        foreach ($typeNames as $typeName) {
            $reflector = new ReflectionClass($typeName);

            if ($reflector->isFinal()) {
                continue;
            }

            switch ($typeName) {
                // works in special cases
                case 'SimpleXMLElement':
                case 'SimpleXMLIterator':

                // unsupported
                case '__PHP_Incomplete_Class':

                // php bugs
                case 'DatePeriod':
                case 'IntlCalendar':
                case 'IntlGregorianCalendar':

                    continue 2;
            }

            if ($isHhvm) {
                switch ($typeName) {
                    // for some reason, Closure is not a final class in HHVM
                    case 'Closure':

                    // WaitHandles may not be directly instantiated
                    case 'HH\WaitableWaitHandle':
                    case 'HH\WaitHandle':
                    case 'HH\ResumableWaitHandle':

                        continue 2;
                }
            }

            $data[$typeName] = array($typeName);
        }

        return $data;
    }

    /**
     * @dataProvider typeData
     */
    public function testTypes($typeName)
    {
        // echo "class $typeName\n";
        // ob_flush();
        // echo x\mockBuilder($typeName)->source();

        $handle = x\mock($typeName);
        $mock = $handle->get();

        $this->assertTrue($mock instanceof $typeName);
    }

    public function functionData()
    {
        $data = array();

        $functionNames = get_defined_functions();
        $functionNames = array_merge($functionNames['internal'], $functionNames['user']);

        foreach ($functionNames as $functionName) {
            if (false !== strpos($functionName, '\\')) {
                continue;
            }

            $data[$functionName] = array($functionName);
        }

        return $data;
    }

    /**
     * @dataProvider functionData
     */
    public function testFunctions($functionName)
    {
        // echo "function $functionName\n";
        // ob_flush();

        $stub = x\stubGlobal($functionName, 'Eloquent\Phony\Test\EdgeCase');

        $this->assertTrue((bool) $stub);
    }
}
