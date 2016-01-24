<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2016 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Integration\Phake;

use Eloquent\Phony\Matcher\WildcardMatcher;
use Eloquent\Phony\Matcher\WrappedMatcher;
use Phake;
use PHPUnit_Framework_TestCase;
use ReflectionClass;

class PhakeMatcherDriverTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->subject = new PhakeMatcherDriver();

        $this->matcher = Phake::equalTo('x');
    }

    public function testIsAvailable()
    {
        $this->assertTrue($this->subject->isAvailable());
    }

    public function testMatcherClassNames()
    {
        $this->assertSame(
            array(
                'Phake_Matchers_IArgumentMatcher',
                'Phake_Matchers_IChainableArgumentMatcher',
            ),
            $this->subject->matcherClassNames()
        );
    }

    public function testWrapMatcher()
    {
        $this->assertEquals(new WrappedMatcher($this->matcher), $this->subject->wrapMatcher($this->matcher));
    }

    public function testWrapMatcherWildcard()
    {
        $this->matcher = Phake::anyParameters();

        $this->assertSame(WildcardMatcher::instance(), $this->subject->wrapMatcher($this->matcher));
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
