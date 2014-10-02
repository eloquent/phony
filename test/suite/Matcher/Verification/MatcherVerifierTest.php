<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Matcher\Verification;

use Eloquent\Phony\Matcher\EqualToMatcher;
use Eloquent\Phony\Matcher\Factory\MatcherFactory;
use Eloquent\Phony\Matcher\WildcardMatcher;
use PHPUnit_Framework_TestCase;
use ReflectionClass;

class MatcherVerifierTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->subject = new MatcherVerifier();

        $this->matcherFactory = new MatcherFactory();
        $this->arguments = array('a', 'b', 'c');
    }

    public function matchesData()
    {
        //                                    arguments                  isValid
        return array(
            'Exact arguments'        => array(array('a', 'b', 'c'),      true),
            'First arguments'        => array(array('a', 'b'),           false),
            'Single argument'        => array(array('a'),                false),
            'Last arguments'         => array(array('b', 'c'),           false),
            'Last argument'          => array(array('c'),                false),
            'Extra arguments'        => array(array('a', 'b', 'c', 'd'), false),
            'First argument differs' => array(array('d', 'b', 'c'),      false),
            'Last argument differs'  => array(array('a', 'b', 'd'),      false),
            'Unused argument'        => array(array('d'),                false),
        );
    }

    /**
     * @dataProvider matchesData
     */
    public function testMatches(array $arguments, $isValid)
    {
        $matchers = $this->matcherFactory->adaptAll($arguments);

        $this->assertSame($isValid, $this->subject->matches($matchers, $this->arguments));
    }

    public function testMatchesWithWildcardAfterValue()
    {
        $matchers = array(new EqualToMatcher('a'), new WildcardMatcher(new EqualToMatcher('b')));

        $this->assertTrue($this->subject->matches($matchers, array('a')));
        $this->assertTrue($this->subject->matches($matchers, array('a', 'b')));
        $this->assertTrue($this->subject->matches($matchers, array('a', 'b', 'b')));
        $this->assertFalse($this->subject->matches($matchers, array('a', 'x')));
        $this->assertFalse($this->subject->matches($matchers, array('a', 'b', 'x')));
        $this->assertFalse($this->subject->matches($matchers, array('a', 'x', 'b')));
        $this->assertFalse($this->subject->matches($matchers, array('a', 'x', 'x')));
    }

    public function testMatchesWithWildcardBeforeValue()
    {
        $matchers = array(new WildcardMatcher(new EqualToMatcher('b')), new EqualToMatcher('a'));

        $this->assertTrue($this->subject->matches($matchers, array('a')));
        $this->assertTrue($this->subject->matches($matchers, array('b', 'a')));
        $this->assertTrue($this->subject->matches($matchers, array('b', 'b', 'a')));
        $this->assertFalse($this->subject->matches($matchers, array('x', 'a')));
        $this->assertFalse($this->subject->matches($matchers, array('b', 'x', 'a')));
        $this->assertFalse($this->subject->matches($matchers, array('x', 'b', 'a')));
        $this->assertFalse($this->subject->matches($matchers, array('x', 'x', 'a')));
    }

    public function testMatchesWithWildcardBeforeValueGreedy()
    {
        $matchers = array(new WildcardMatcher(new EqualToMatcher('a')), new EqualToMatcher('a'));

        $this->assertFalse($this->subject->matches($matchers, array('a', 'a')));
    }

    public function testMatchesWithOnlyWildcard()
    {
        $matchers = array(new WildcardMatcher(new EqualToMatcher('b')));

        $this->assertTrue($this->subject->matches($matchers, array()));
        $this->assertTrue($this->subject->matches($matchers, array('b')));
        $this->assertTrue($this->subject->matches($matchers, array('b', 'b')));
        $this->assertFalse($this->subject->matches($matchers, array('x')));
        $this->assertFalse($this->subject->matches($matchers, array('b', 'x')));
        $this->assertFalse($this->subject->matches($matchers, array('x', 'b')));
        $this->assertFalse($this->subject->matches($matchers, array('x', 'x')));
    }

    public function testMatchesWithWildcardMinimumArguments()
    {
        $matchers = array(new EqualToMatcher('a'), new WildcardMatcher(new EqualToMatcher('b'), 1));

        $this->assertFalse($this->subject->matches($matchers, array('a')));
        $this->assertTrue($this->subject->matches($matchers, array('a', 'b')));
        $this->assertTrue($this->subject->matches($matchers, array('a', 'b', 'b')));
        $this->assertFalse($this->subject->matches($matchers, array('a', 'x')));
        $this->assertFalse($this->subject->matches($matchers, array('a', 'b', 'x')));
        $this->assertFalse($this->subject->matches($matchers, array('a', 'x', 'b')));
        $this->assertFalse($this->subject->matches($matchers, array('a', 'x', 'x')));
    }

    public function testMatchesWithWildcardMaximumArguments()
    {
        $matchers = array(new EqualToMatcher('a'), new WildcardMatcher(new EqualToMatcher('b'), null, 1));

        $this->assertTrue($this->subject->matches($matchers, array('a')));
        $this->assertTrue($this->subject->matches($matchers, array('a', 'b')));
        $this->assertFalse($this->subject->matches($matchers, array('a', 'b', 'b')));
        $this->assertFalse($this->subject->matches($matchers, array('a', 'x')));
        $this->assertFalse($this->subject->matches($matchers, array('a', 'b', 'x')));
        $this->assertFalse($this->subject->matches($matchers, array('a', 'x', 'b')));
        $this->assertFalse($this->subject->matches($matchers, array('a', 'x', 'x')));
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
