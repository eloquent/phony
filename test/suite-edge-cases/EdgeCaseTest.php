<?php

use function Eloquent\Phony\mock;
use function Eloquent\Phony\partialMock;
use Eloquent\Phony\Reflection\FeatureDetector;
use function Eloquent\Phony\stubGlobal;
use Eloquent\Phony\Test\EdgeCase as TestNamespace;
use PHPUnit\Framework\TestCase;

class EdgeCaseTest extends TestCase
{
    protected function setUp(): void
    {
        $this->featureDetector = FeatureDetector::instance();
    }

    public function testSimpleXmlElementFull()
    {
        if (!class_exists('SimpleXMLElement')) {
            $this->markTestSkipped('Requires the SimpleXMLElement class.');
        }

        $full = mock('SimpleXMLElement');
        $mock = $full->get();

        $this->assertInstanceOf(SimpleXMLElement::class, $mock);
    }

    public function testSimpleXmlElementPartial()
    {
        if (!class_exists('SimpleXMLElement')) {
            $this->markTestSkipped('Requires the SimpleXMLElement class.');
        }

        $partial = partialMock('SimpleXMLElement', ['<root><sub></sub></root>']);
        $mock = $partial->get();

        $this->assertInstanceOf(SimpleXMLElement::class, $mock);
        $this->assertInstanceOf(SimpleXMLElement::class, $mock->sub);
    }

    public function testSimpleXmlIteratorFull()
    {
        if (!class_exists('SimpleXMLIterator')) {
            $this->markTestSkipped('Requires the SimpleXMLIterator class.');
        }

        $full = mock('SimpleXMLIterator');
        $mock = $full->get();

        $this->assertInstanceOf(SimpleXMLIterator::class, $mock);
    }

    public function testSimpleXmlIteratorPartial()
    {
        if (!class_exists('SimpleXMLIterator')) {
            $this->markTestSkipped('Requires the SimpleXMLIterator class.');
        }

        $partial = partialMock('SimpleXMLIterator', ['<root><sub></sub></root>']);
        $mock = $partial->get();

        $this->assertInstanceOf(SimpleXMLIterator::class, $mock);
        $this->assertInstanceOf(SimpleXMLElement::class, $mock->sub);
    }

    public function typeData()
    {
        $data = [];
        $typeNames = array_merge(get_declared_classes(), get_declared_interfaces());

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

            $data[$typeName] = [$typeName];
        }

        return $data;
    }

    /**
     * @dataProvider typeData
     */
    public function testTypes($typeName)
    {
        // echo "class $typeName\n";
        // echo mockBuilder($typeName)->source();
        // ob_flush();

        $handle = mock($typeName);
        $mock = $handle->get();

        $this->assertInstanceOf($typeName, $mock);
    }

    public function functionData()
    {
        $data = [];

        $functionNames = get_defined_functions();
        $functionNames = array_merge($functionNames['internal'], $functionNames['user']);

        foreach ($functionNames as $functionName) {
            if (false !== strpos($functionName, '\\')) {
                continue;
            }

            switch ($functionName) {
                case 'assert':
                    continue 2;
            }

            $data[$functionName] = [$functionName];
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

        $stub = stubGlobal($functionName, TestNamespace::class);

        $this->assertTrue((bool) $stub);
    }
}
