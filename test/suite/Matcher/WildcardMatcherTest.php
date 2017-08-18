<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2017 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Matcher;

use Eloquent\Phony\Exporter\InlineExporter;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class WildcardMatcherTest extends TestCase
{
    protected function setUp()
    {
        $this->exporter = InlineExporter::instance();
        $this->matcher = new EqualToMatcher('x', true, $this->exporter);
        $this->minimumArguments = 1;
        $this->maximumArguments = 2;
        $this->subject = new WildcardMatcher($this->matcher, $this->minimumArguments, $this->maximumArguments);
    }

    public function testConstructor()
    {
        $this->assertSame($this->matcher, $this->subject->matcher());
        $this->assertSame($this->minimumArguments, $this->subject->minimumArguments());
        $this->assertSame($this->maximumArguments, $this->subject->maximumArguments());
    }

    public function describeData()
    {
        $this->exporter = InlineExporter::instance();
        $this->matcher = new EqualToMatcher('x', true, $this->exporter);

        //                                   matcher                 minimum maximum expected
        return [
            'Any amount of anything'     => [AnyMatcher::instance(), 0,      -1,     '<any>*'],
            'Any amount of equal to'     => [$this->matcher,         0,      -1,     '"x"*'],
            'Minimum amount of anything' => [AnyMatcher::instance(), 111,    -1,     '<any>{111,}'],
            'Maximum amount of anything' => [AnyMatcher::instance(), 0,      111,    '<any>{,111}'],
            'Range of anything'          => [AnyMatcher::instance(), 111,    222,    '<any>{111,222}'],
            'Exact amount of anything'   => [AnyMatcher::instance(), 111,    111,    '<any>{111}'],
        ];
    }

    /**
     * @dataProvider describeData
     */
    public function testDescribe($matcher, $minimumArguments, $maximumArguments, $expected)
    {
        $this->subject = new WildcardMatcher($matcher, $minimumArguments, $maximumArguments);

        $this->assertSame($expected, $this->subject->describe());
        $this->assertSame($expected, strval($this->subject));
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
