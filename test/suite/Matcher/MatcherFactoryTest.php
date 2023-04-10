<?php

declare(strict_types=1);

namespace Eloquent\Phony\Matcher;

use AllowDynamicProperties;
use Eloquent\Phony\Test\Facade\FacadeContainer;
use Eloquent\Phony\Test\TestClassA;
use Eloquent\Phony\Test\TestMatcherA;
use Eloquent\Phony\Test\TestMatcherB;
use Eloquent\Phony\Test\TestMatcherDriverA;
use Eloquent\Phony\Test\TestMatcherDriverB;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

#[AllowDynamicProperties]
class MatcherFactoryTest extends TestCase
{
    protected function setUp(): void
    {
        $this->container = new FacadeContainer();
        $this->anyMatcher = $this->container->anyMatcher;
        $this->wildcardMatcher = $this->container->wildcardMatcher;
        $this->exporter = $this->container->exporter;

        $this->subject = $this->container->matcherFactory;

        $this->hamcrestDriver = $this->container->hamcrestMatcherDriver;
        $this->driverA = new TestMatcherDriverA($this->exporter);
        $this->driverB = new TestMatcherDriverB($this->exporter);
    }

    public function testAddMatcherDriver()
    {
        $this->subject->addMatcherDriver($this->driverA);

        $this->assertSame([$this->hamcrestDriver, $this->driverA], $this->subject->drivers());

        $this->subject->addMatcherDriver($this->driverB);

        $this->assertSame([$this->hamcrestDriver, $this->driverA, $this->driverB], $this->subject->drivers());
    }

    public function testIsMatcher()
    {
        $this->subject->addMatcherDriver($this->driverA);
        $this->subject->addMatcherDriver($this->driverB);

        $this->assertTrue($this->subject->isMatcher(new EqualToMatcher('a', true, $this->exporter)));
        $this->assertTrue($this->subject->isMatcher(new TestMatcherA()));
        $this->assertTrue($this->subject->isMatcher(new TestMatcherB()));
        $this->assertFalse($this->subject->isMatcher((object) []));
    }

    public function testAdapt()
    {
        $value = (object) ['key' => 'value'];
        $matcher = new EqualToMatcher($value, true, $this->exporter);
        $adaptedValue = $this->subject->adapt($value);

        $this->assertSame($matcher, $this->subject->adapt($matcher));
        $this->assertNotSame($matcher, $adaptedValue);
        $this->assertEquals($matcher, $adaptedValue);
    }

    public function testAdaptBoolean()
    {
        $value = true;
        $matcher = new EqualToMatcher($value, true, $this->exporter);
        $adaptedValue = $this->subject->adapt($value);

        $this->assertEquals($matcher, $adaptedValue);
    }

    public function testAdaptViaDriver()
    {
        $this->subject->addMatcherDriver($this->driverA);
        $this->subject->addMatcherDriver($this->driverB);
        $driverAMatcher = new TestMatcherA();
        $driverBMatcher = new TestMatcherB();

        $this->assertEquals(new EqualToMatcher('a', false, $this->exporter), $this->subject->adapt($driverAMatcher));
        $this->assertEquals(new EqualToMatcher('b', false, $this->exporter), $this->subject->adapt($driverBMatcher));
    }

    public function testAdaptSpecialCases()
    {
        $this->assertSame($this->anyMatcher, $this->subject->adapt('~'));
    }

    public function testAdaptSet()
    {
        $this->subject->addMatcherDriver($this->driverA);
        $this->subject->addMatcherDriver($this->driverB);

        $valueB = new EqualToMatcher('b', true, $this->exporter);
        $valueC = (object) [];
        $valueD = $this->container->handleFactory->instanceHandle(
            $this->container->mockBuilderFactory->create()->full()
        );
        $values = [
            'a',
            $valueB,
            $valueC,
            $valueD,
            new TestMatcherA(),
            '~',
            '*',
        ];
        $actual = $this->subject->adaptSet(['a', 'b'], $values);
        $expected = new MatcherSet(
            parameterNames: ['a', 'b'],
            keyMap: [
                'a' => 0,
                0 => 'a',
                'b' => 1,
                1 => 'b',
            ],
            declaredCount: 2,
            declaredMatchers: [
                new EqualToMatcher('a', true, $this->exporter),
                $valueB,
            ],
            variadicMatchers: [
                2 => new EqualToMatcher($valueC, true, $this->exporter),
                3 => new EqualToMatcher($valueD, true, $this->exporter),
                4 => new EqualToMatcher('a', false, $this->exporter),
                5 => $this->anyMatcher,
            ],
            wildcardMatcher: $this->wildcardMatcher,
            wildcardInnerMatcher: $this->anyMatcher,
            wildcardMinimum: 0,
            wildcardMaximum: -1,
        );

        $this->assertEquals($expected, $actual);
    }

    public function testAdaptSetWithNamedMatchers()
    {
        $this->subject->addMatcherDriver($this->driverA);
        $this->subject->addMatcherDriver($this->driverB);

        $valueB = new EqualToMatcher('b', true, $this->exporter);
        $valueC = (object) [];
        $valueD = $this->container->handleFactory->instanceHandle(
            $this->container->mockBuilderFactory->create()->full()
        );
        $values = [
            '*',
            'a' => 'a',
            'b' => $valueB,
            'c' => $valueC,
            'd' => $valueD,
            'e' => new TestMatcherA(),
            'f' => '~',
        ];
        $actual = $this->subject->adaptSet(['a', 'b'], $values);
        $expected = new MatcherSet(
            parameterNames: ['a', 'b'],
            keyMap: [
                'a' => 0,
                0 => 'a',
                'b' => 1,
                1 => 'b',
            ],
            declaredCount: 2,
            declaredMatchers: [
                new EqualToMatcher('a', true, $this->exporter),
                $valueB,
            ],
            variadicMatchers: [
                'c' => new EqualToMatcher($valueC, true, $this->exporter),
                'd' => new EqualToMatcher($valueD, true, $this->exporter),
                'e' => new EqualToMatcher('a', false, $this->exporter),
                'f' => $this->anyMatcher,
            ],
            wildcardMatcher: $this->wildcardMatcher,
            wildcardInnerMatcher: $this->anyMatcher,
            wildcardMinimum: 0,
            wildcardMaximum: -1,
        );

        $this->assertEquals($expected, $actual);
    }

    public function adaptSetInvalidInputData(): array
    {
        return [
            'positional after named' => [
                'Cannot use a positional matcher after a named matcher.',
                [],
                ['a' => 1, 2],
            ],
            'positional after wildcard' => [
                'Cannot use a positional matcher after a wildcard matcher.',
                [],
                ['*', 2],
            ],
            'wildcard after named' => [
                'Cannot use a wildcard matcher after a named matcher.',
                [],
                ['a' => 1, '*'],
            ],
            'wildcard after wildcard' => [
                'Cannot use a wildcard matcher after a wildcard matcher.',
                [],
                ['*', '*'],
            ],
            'named overwrites previous' => [
                'Named matcher a overwrites previous matcher.',
                ['a'],
                [1, 'a' => 2],
            ],
            'named wildcard' => [
                'Cannot use a named wildcard matcher.',
                ['a'],
                ['a' => '*'],
            ],
        ];
    }

    /**
     * @dataProvider adaptSetInvalidInputData
     */
    public function testAdaptSetWithInvalidInput(string $expected, array $parameterNames, array $values)
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($expected);

        $this->subject->adaptSet($parameterNames, $values);
    }

    public function testEqualTo()
    {
        $expected = new EqualToMatcher('x', false, $this->exporter);

        $this->assertEquals($expected, $this->subject->equalTo('x'));
    }

    public function testAnInstanceOf()
    {
        $expected = new InstanceOfMatcher(TestClassA::class);

        $this->assertEquals($expected, $this->subject->anInstanceOf(TestClassA::class));
        $this->assertEquals($expected, $this->subject->anInstanceOf(new TestClassA()));
    }

    public function testWildcard()
    {
        $expected = new WildcardMatcher(new EqualToMatcher('x', true, $this->exporter), 111, 222);

        $this->assertEquals($expected, $this->subject->wildcard('x', 111, 222));
    }

    public function testWildcardWithNullValue()
    {
        $expected = new WildcardMatcher($this->anyMatcher, 111, 222);

        $this->assertEquals($expected, $this->subject->wildcard(null, 111, 222));
    }
}
