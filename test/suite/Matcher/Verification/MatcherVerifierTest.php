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
        $this->arguments = array('argumentA', 'argumentB', 'argumentC');
    }

    public function matchesData()
    {
        //                                    arguments                                                  isValid
        return array(
            'Exact arguments'        => array(array('argumentA', 'argumentB', 'argumentC'),              true),
            'First arguments'        => array(array('argumentA', 'argumentB'),                           false),
            'Single argument'        => array(array('argumentA'),                                        false),
            'Last arguments'         => array(array('argumentB', 'argumentC'),                           false),
            'Last argument'          => array(array('argumentC'),                                        false),
            'Extra arguments'        => array(array('argumentA', 'argumentB', 'argumentC', 'argumentD'), false),
            'First argument differs' => array(array('argumentD', 'argumentB', 'argumentC'),              false),
            'Last argument differs'  => array(array('argumentA', 'argumentB', 'argumentD'),              false),
            'Unused argument'        => array(array('argumentD'),                                        false),
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
        $matchers = array(new EqualToMatcher('valueA'), new WildcardMatcher(new EqualToMatcher('valueB')));

        $this->assertTrue($this->subject->matches($matchers, array('valueA')));
        $this->assertTrue($this->subject->matches($matchers, array('valueA', 'valueB')));
        $this->assertTrue($this->subject->matches($matchers, array('valueA', 'valueB', 'valueB')));
        $this->assertFalse($this->subject->matches($matchers, array('valueA', 'anotherValue')));
        $this->assertFalse($this->subject->matches($matchers, array('valueA', 'valueB', 'anotherValue')));
        $this->assertFalse($this->subject->matches($matchers, array('valueA', 'anotherValue', 'valueB')));
        $this->assertFalse($this->subject->matches($matchers, array('valueA', 'anotherValue', 'anotherValue')));
    }

    public function testMatchesWithWildcardBeforeValue()
    {
        $matchers = array(new WildcardMatcher(new EqualToMatcher('valueB')), new EqualToMatcher('valueA'));

        $this->assertTrue($this->subject->matches($matchers, array('valueA')));
        $this->assertTrue($this->subject->matches($matchers, array('valueB', 'valueA')));
        $this->assertTrue($this->subject->matches($matchers, array('valueB', 'valueB', 'valueA')));
        $this->assertFalse($this->subject->matches($matchers, array('anotherValue', 'valueA')));
        $this->assertFalse($this->subject->matches($matchers, array('valueB', 'anotherValue', 'valueA')));
        $this->assertFalse($this->subject->matches($matchers, array('anotherValue', 'valueB', 'valueA')));
        $this->assertFalse($this->subject->matches($matchers, array('anotherValue', 'anotherValue', 'valueA')));
    }

    public function testMatchesWithWildcardBeforeValueGreedy()
    {
        $matchers = array(new WildcardMatcher(new EqualToMatcher('valueA')), new EqualToMatcher('valueA'));

        $this->assertFalse($this->subject->matches($matchers, array('valueA', 'valueA')));
    }

    public function testMatchesWithOnlyWildcard()
    {
        $matchers = array(new WildcardMatcher(new EqualToMatcher('valueB')));

        $this->assertTrue($this->subject->matches($matchers, array()));
        $this->assertTrue($this->subject->matches($matchers, array('valueB')));
        $this->assertTrue($this->subject->matches($matchers, array('valueB', 'valueB')));
        $this->assertFalse($this->subject->matches($matchers, array('anotherValue')));
        $this->assertFalse($this->subject->matches($matchers, array('valueB', 'anotherValue')));
        $this->assertFalse($this->subject->matches($matchers, array('anotherValue', 'valueB')));
        $this->assertFalse($this->subject->matches($matchers, array('anotherValue', 'anotherValue')));
    }

    public function testMatchesWithWildcardMinimumArguments()
    {
        $matchers = array(new EqualToMatcher('valueA'), new WildcardMatcher(new EqualToMatcher('valueB'), 1));

        $this->assertFalse($this->subject->matches($matchers, array('valueA')));
        $this->assertTrue($this->subject->matches($matchers, array('valueA', 'valueB')));
        $this->assertTrue($this->subject->matches($matchers, array('valueA', 'valueB', 'valueB')));
        $this->assertFalse($this->subject->matches($matchers, array('valueA', 'anotherValue')));
        $this->assertFalse($this->subject->matches($matchers, array('valueA', 'valueB', 'anotherValue')));
        $this->assertFalse($this->subject->matches($matchers, array('valueA', 'anotherValue', 'valueB')));
        $this->assertFalse($this->subject->matches($matchers, array('valueA', 'anotherValue', 'anotherValue')));
    }

    public function testMatchesWithWildcardMaximumArguments()
    {
        $matchers = array(new EqualToMatcher('valueA'), new WildcardMatcher(new EqualToMatcher('valueB'), null, 1));

        $this->assertTrue($this->subject->matches($matchers, array('valueA')));
        $this->assertTrue($this->subject->matches($matchers, array('valueA', 'valueB')));
        $this->assertFalse($this->subject->matches($matchers, array('valueA', 'valueB', 'valueB')));
        $this->assertFalse($this->subject->matches($matchers, array('valueA', 'anotherValue')));
        $this->assertFalse($this->subject->matches($matchers, array('valueA', 'valueB', 'anotherValue')));
        $this->assertFalse($this->subject->matches($matchers, array('valueA', 'anotherValue', 'valueB')));
        $this->assertFalse($this->subject->matches($matchers, array('valueA', 'anotherValue', 'anotherValue')));
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
