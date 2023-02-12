<?php

declare(strict_types=1);

namespace Eloquent\Phony\Stub;

use Eloquent\Phony\Exporter\InlineExporter;
use Eloquent\Phony\Invocation\InvocableInspector;
use Eloquent\Phony\Invocation\Invoker;
use Eloquent\Phony\Matcher\MatcherFactory;
use Eloquent\Phony\Matcher\MatcherVerifier;
use Eloquent\Phony\Reflection\FeatureDetector;
use Eloquent\Phony\Stub\Answer\Builder\GeneratorAnswerBuilder;
use Eloquent\Phony\Stub\Answer\Builder\GeneratorAnswerBuilderFactory;
use Eloquent\Phony\Test\WithDynamicProperties;
use Generator;
use PHPUnit\Framework\TestCase;

class StubDataWithGeneratorsTest extends TestCase
{
    use WithDynamicProperties;

    private array $callsA;
    private array $callsB;
    private array $callsC;
    private array $callsD;
    private array $callsE;
    private array $callsF;

    private int $callCountA;
    private int $callCountB;
    private int $callCountC;
    private int $callCountD;
    private int $callCountE;
    private int $callCountF;

    protected function setUp(): void
    {
        $this->callback = 'implode';
        $this->label = 'label';
        $this->defaultAnswerCallback = function ($stub) {
            $stub->returns('default answer');
        };
        $this->matcherFactory = MatcherFactory::instance();
        $this->matcherVerifier = new MatcherVerifier();
        $this->invoker = new Invoker();
        $this->invocableInspector = new InvocableInspector();
        $this->featureDetector = FeatureDetector::instance();
        $this->emptyValueFactory = new EmptyValueFactory($this->featureDetector);
        $this->generatorAnswerBuilderFactory = GeneratorAnswerBuilderFactory::instance();
        $this->exporter = InlineExporter::instance();
        $this->subject = new StubData(
            $this->callback,
            $this->label,
            $this->defaultAnswerCallback,
            $this->matcherFactory,
            $this->matcherVerifier,
            $this->invoker,
            $this->invocableInspector,
            $this->emptyValueFactory,
            $this->generatorAnswerBuilderFactory,
            $this->exporter
        );

        $this->self = (object) [];
        $this->subject->setSelf($this->self);

        $this->callsA = [];
        $callsA = &$this->callsA;
        $this->callCountA = 0;
        $callCountA = &$this->callCountA;
        $this->callbackA = function () use (&$callsA, &$callCountA) {
            $arguments = func_get_args();
            $callsA[] = $arguments;
            ++$callCountA;

            array_unshift($arguments, 'A');

            return $arguments;
        };

        $this->callsB = [];
        $callsB = &$this->callsB;
        $this->callCountB = 0;
        $callCountB = &$this->callCountB;
        $this->callbackB = function () use (&$callsB, &$callCountB) {
            $arguments = func_get_args();
            $callsB[] = $arguments;
            ++$callCountB;

            array_unshift($arguments, 'B');

            return $arguments;
        };

        $this->callsC = [];
        $callsC = &$this->callsC;
        $this->callCountC = 0;
        $callCountC = &$this->callCountC;
        $this->callbackC = function () use (&$callsC, &$callCountC) {
            $arguments = func_get_args();
            $callsC[] = $arguments;
            ++$callCountC;

            array_unshift($arguments, 'C');

            return $arguments;
        };

        $this->callsD = [];
        $callsD = &$this->callsD;
        $this->callCountD = 0;
        $callCountD = &$this->callCountD;
        $this->callbackD = function () use (&$callsD, &$callCountD) {
            $arguments = func_get_args();
            $callsD[] = $arguments;
            ++$callCountD;

            array_unshift($arguments, 'D');

            return $arguments;
        };

        $this->callsE = [];
        $callsE = &$this->callsE;
        $this->callCountE = 0;
        $callCountE = &$this->callCountE;
        $this->callbackE = function () use (&$callsE, &$callCountE) {
            $arguments = func_get_args();
            $callsE[] = $arguments;
            ++$callCountE;

            array_unshift($arguments, 'E');

            return $arguments;
        };

        $this->callsF = [];
        $callsF = &$this->callsF;
        $this->callCountF = 0;
        $callCountF = &$this->callCountF;
        $this->callbackF = function () use (&$callsF, &$callCountF) {
            $arguments = func_get_args();
            $callsF[] = $arguments;
            ++$callCountF;

            array_unshift($arguments, 'F');

            return $arguments;
        };

        $this->referenceCallback = function (&$a, &$b = null, &$c = null, &$d = null) {
            $a = 'a';
            $b = 'b';
            $c = 'c';
            $d = 'd';
        };
    }

    public function testGenerates()
    {
        $builder = $this->subject->generates(['a' => 'b', 'c']);
        $generator = call_user_func($this->subject);
        $actual = iterator_to_array($generator);

        $this->assertInstanceOf(GeneratorAnswerBuilder::class, $builder);
        $this->assertInstanceOf(Generator::class, $generator);
        $this->assertSame($this->subject, $builder->returns());
        $this->assertSame(['a' => 'b', 0 => 'c'], $actual);

        $generator = call_user_func($this->subject);
        $actual = iterator_to_array($generator);

        $this->assertInstanceOf(Generator::class, $generator);
        $this->assertSame($this->subject, $builder->returns());
        $this->assertSame(['a' => 'b', 0 => 'c'], $actual);
    }

    public function testGeneratesWithMultipleArguments()
    {
        $builder = $this->subject->generates(['a'], ['b']);
        $actualA = iterator_to_array(call_user_func($this->subject));
        $actualB = iterator_to_array(call_user_func($this->subject));
        $actualC = iterator_to_array(call_user_func($this->subject));

        $this->assertInstanceOf(GeneratorAnswerBuilder::class, $builder);
        $this->assertSame(['a'], $actualA);
        $this->assertSame(['b'], $actualB);
        $this->assertSame(['b'], $actualC);
    }
}
