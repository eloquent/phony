<?php

namespace Eloquent\Phony\Hook;

use Eloquent\Phony\Invocation\InvocableInspector;
use Eloquent\Phony\Reflection\FeatureDetector;
use Eloquent\Phony\Reflection\FunctionSignatureInspectorFactory;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class FunctionHookGeneratorTest extends TestCase
{
    protected function setUp()
    {
        $this->subject = new FunctionHookGenerator();

        $this->invocableInspector = InvocableInspector::instance();
        $this->signatureInspector = FunctionSignatureInspectorFactory::create();
    }

    public function generateData()
    {
        $fixturePath = __DIR__ . '/../../fixture/hook-generator';
        $data = [];

        foreach (scandir($fixturePath) as $testName) {
            if ('.' === $testName[0]) {
                continue;
            }

            $data[$testName] = [$testName];
        }

        return $data;
    }

    /**
     * @dataProvider generateData
     */
    public function testGenerate($testName)
    {
        $fixturePath = __DIR__ . '/../../fixture/hook-generator';

        $detector = FeatureDetector::instance();
        $supportedPath = $fixturePath . '/' . $testName . '/supported.php';

        if (is_file($supportedPath)) {
            $isSupported = require $supportedPath;

            if (!$isSupported) {
                $this->markTestSkipped($message);
            }
        }

        require $fixturePath . '/' . $testName . '/callback.php';
        $expected = file_get_contents($fixturePath . '/' . $testName . '/expected.php');
        $expected = str_replace("\n", PHP_EOL, $expected);
        $signature = $this->signatureInspector->signature($this->invocableInspector->callbackReflector($callback));
        $actual = $this->subject->generateHook($functionName, $namespace, $signature);

        $this->assertSame($expected, '<?php' . PHP_EOL . PHP_EOL . $actual);
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
