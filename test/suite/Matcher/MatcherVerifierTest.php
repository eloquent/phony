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

use PHPUnit_Framework_TestCase;
use ReflectionClass;

class MatcherVerifierTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->subject = new MatcherVerifier();

        $this->matcherFactory = MatcherFactory::instance();
        $this->arguments = array('a', 'b', 'c');
    }

    public function matchesData()
    {
        //                                    matchers                   isValid
        return array(
            'Exact arguments'        => array(array('a', 'b', 'c'),      true),
            'Empty'                  => array(array(),                   false),
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
        $explain = $this->subject->explain($matchers, $this->arguments);

        $this->assertSame($isValid, $this->subject->matches($matchers, $this->arguments));
        $this->assertSame($isValid, $explain->isMatch);
    }

    public function testMatchesExplicitArgumentExistence()
    {
        $matchers = array($this->matcherFactory->equalTo(null));

        $this->assertTrue($this->subject->matches($matchers, array(null)));
        $this->assertFalse($this->subject->matches($matchers, array()));
    }

    public function testMatchesWithWildcardAfterValue()
    {
        $matchers = array(
            $this->matcherFactory->equalTo('a'),
            new WildcardMatcher($this->matcherFactory->equalTo('b'), 0, null),
        );

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
        $matchers = array(
            new WildcardMatcher($this->matcherFactory->equalTo('b'), 0, null),
            $this->matcherFactory->equalTo('a'),
        );

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
        $matchers = array(
            new WildcardMatcher($this->matcherFactory->equalTo('a'), 0, null),
            $this->matcherFactory->equalTo('a'),
        );

        $this->assertFalse($this->subject->matches($matchers, array('a', 'a')));
    }

    public function testMatchesWithOnlyWildcard()
    {
        $matchers = array(new WildcardMatcher($this->matcherFactory->equalTo('b'), 0, null));

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
        $matchers = array(
            $this->matcherFactory->equalTo('a'),
            new WildcardMatcher($this->matcherFactory->equalTo('b'), 1, null),
        );

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
        $matchers = array(
            $this->matcherFactory->equalTo('a'),
            new WildcardMatcher($this->matcherFactory->equalTo('b'), 0, 1),
        );

        $this->assertTrue($this->subject->matches($matchers, array('a')));
        $this->assertTrue($this->subject->matches($matchers, array('a', 'b')));
        $this->assertFalse($this->subject->matches($matchers, array('a', 'b', 'b')));
        $this->assertFalse($this->subject->matches($matchers, array('a', 'x')));
        $this->assertFalse($this->subject->matches($matchers, array('a', 'b', 'x')));
        $this->assertFalse($this->subject->matches($matchers, array('a', 'x', 'b')));
        $this->assertFalse($this->subject->matches($matchers, array('a', 'x', 'x')));
    }

    public function explainData()
    {
        //                                    matchers                   isMatch matcherMatches                  argumentMatches
        return array(
            'Exact arguments'        => array(array('a', 'b', 'c'),      true,   array(true, true, true),        array(true, true, true)),
            'Empty'                  => array(array(),                   false,  array(),                        array(false, false, false)),
            'First arguments'        => array(array('a', 'b'),           false,  array(true, true),              array(true, true, false)),
            'Single argument'        => array(array('a'),                false,  array(true),                    array(true, false, false)),
            'Last arguments'         => array(array('b', 'c'),           false,  array(false, false),            array(false, false, false)),
            'Last argument'          => array(array('c'),                false,  array(false),                   array(false, false, false)),
            'Extra arguments'        => array(array('a', 'b', 'c', 'd'), false,  array(true, true, true, false), array(true, true, true, false)),
            'First argument differs' => array(array('d', 'b', 'c'),      false,  array(false, true, true),       array(false, true, true)),
            'Last argument differs'  => array(array('a', 'b', 'd'),      false,  array(true, true, false),       array(true, true, false)),
            'Unused argument'        => array(array('d'),                false,  array(false),                   array(false, false, false)),
        );
    }

    /**
     * @dataProvider explainData
     */
    public function testExplain(array $arguments, $isMatch, $matcherMatches, $argumentMatches)
    {
        $matchers = $this->matcherFactory->adaptAll($arguments);

        $this->assertEquals(
            new MatcherResult($isMatch, $matcherMatches, $argumentMatches),
            $this->subject->explain($matchers, $this->arguments)
        );
        $this->assertSame($isMatch, $this->subject->matches($matchers, $this->arguments));
    }

    public function testExplainExplicitArgumentExistence()
    {
        $matchers = array($this->matcherFactory->equalTo(null));

        $this->assertEquals(
            new MatcherResult(true, array(true), array(true)),
            $this->subject->explain($matchers, array(null))
        );
        $this->assertEquals(
            new MatcherResult(false, array(false), array(false)),
            $this->subject->explain($matchers, array())
        );
    }

    public function testExplainWithWildcardAfterValue()
    {
        $matchers = array(
            $this->matcherFactory->equalTo('a'),
            new WildcardMatcher($this->matcherFactory->equalTo('b'), 0, null),
        );

        $this->assertEquals(
            new MatcherResult(true, array(true, true), array(true)),
            $this->subject->explain($matchers, array('a'))
        );
        $this->assertEquals(
            new MatcherResult(true, array(true, true), array(true, true)),
            $this->subject->explain($matchers, array('a', 'b'))
        );
        $this->assertEquals(
            new MatcherResult(true, array(true, true), array(true, true, true)),
            $this->subject->explain($matchers, array('a', 'b', 'b'))
        );
        $this->assertEquals(
            new MatcherResult(false, array(true, true), array(true, false)),
            $this->subject->explain($matchers, array('a', 'x'))
        );
        $this->assertEquals(
            new MatcherResult(false, array(true, true), array(true, true, false)),
            $this->subject->explain($matchers, array('a', 'b', 'x'))
        );
        $this->assertEquals(
            new MatcherResult(false, array(true, true), array(true, false, false)),
            $this->subject->explain($matchers, array('a', 'x', 'b'))
        );
        $this->assertEquals(
            new MatcherResult(false, array(true, true), array(true, false, false)),
            $this->subject->explain($matchers, array('a', 'x', 'x'))
        );
    }

    public function testExplainWithWildcardBeforeValue()
    {
        $matchers = array(
            new WildcardMatcher($this->matcherFactory->equalTo('b'), 0, null),
            $this->matcherFactory->equalTo('a'),
        );

        $this->assertEquals(
            new MatcherResult(true, array(true, true), array(true)),
            $this->subject->explain($matchers, array('a'))
        );
        $this->assertEquals(
            new MatcherResult(true, array(true, true), array(true, true)),
            $this->subject->explain($matchers, array('b', 'a'))
        );
        $this->assertEquals(
            new MatcherResult(true, array(true, true), array(true, true, true)),
            $this->subject->explain($matchers, array('b', 'b', 'a'))
        );
        $this->assertEquals(
            new MatcherResult(false, array(true, false), array(false, false)),
            $this->subject->explain($matchers, array('x', 'a'))
        );
        $this->assertEquals(
            new MatcherResult(false, array(true, false), array(true, false, false)),
            $this->subject->explain($matchers, array('b', 'x', 'a'))
        );
        $this->assertEquals(
            new MatcherResult(false, array(true, false), array(false, false, false)),
            $this->subject->explain($matchers, array('x', 'b', 'a'))
        );
        $this->assertEquals(
            new MatcherResult(false, array(true, false), array(false, false, false)),
            $this->subject->explain($matchers, array('x', 'x', 'a'))
        );
    }

    public function testExplainWithWildcardBeforeValueGreedy()
    {
        $matchers = array(
            new WildcardMatcher($this->matcherFactory->equalTo('a'), 0, null),
            $this->matcherFactory->equalTo('a'),
        );

        $this->assertEquals(
            new MatcherResult(false, array(true, false), array(true, true, false)),
            $this->subject->explain($matchers, array('a', 'a'))
        );
    }

    public function testExplainWithOnlyWildcard()
    {
        $matchers = array(new WildcardMatcher($this->matcherFactory->equalTo('b'), 0, null));

        $this->assertEquals(
            new MatcherResult(true, array(true), array()),
            $this->subject->explain($matchers, array())
        );
        $this->assertEquals(
            new MatcherResult(true, array(true), array(true)),
            $this->subject->explain($matchers, array('b'))
        );
        $this->assertEquals(
            new MatcherResult(true, array(true), array(true, true)),
            $this->subject->explain($matchers, array('b', 'b'))
        );
        $this->assertEquals(
            new MatcherResult(false, array(true), array(false)),
            $this->subject->explain($matchers, array('x'))
        );
        $this->assertEquals(
            new MatcherResult(false, array(true), array(true, false)),
            $this->subject->explain($matchers, array('b', 'x'))
        );
        $this->assertEquals(
            new MatcherResult(false, array(true), array(false, false)),
            $this->subject->explain($matchers, array('x', 'b'))
        );
        $this->assertEquals(
            new MatcherResult(false, array(true), array(false, false)),
            $this->subject->explain($matchers, array('x', 'x'))
        );
    }

    public function testExplainWithWildcardMinimumArguments()
    {
        $matchers = array(
            $this->matcherFactory->equalTo('a'),
            new WildcardMatcher($this->matcherFactory->equalTo('b'), 2, null),
        );

        $this->assertEquals(
            new MatcherResult(false, array(true, false), array(true, false)),
            $this->subject->explain($matchers, array('a'))
        );
        $this->assertEquals(
            new MatcherResult(false, array(true, false), array(true, true, false)),
            $this->subject->explain($matchers, array('a', 'b'))
        );
        $this->assertEquals(
            new MatcherResult(true, array(true, true), array(true, true, true)),
            $this->subject->explain($matchers, array('a', 'b', 'b'))
        );
        $this->assertEquals(
            new MatcherResult(true, array(true, true), array(true, true, true, true)),
            $this->subject->explain($matchers, array('a', 'b', 'b', 'b'))
        );
        $this->assertEquals(
            new MatcherResult(false, array(true, false), array(true, false, false)),
            $this->subject->explain($matchers, array('a', 'x'))
        );
        $this->assertEquals(
            new MatcherResult(false, array(true, false), array(true, true, false)),
            $this->subject->explain($matchers, array('a', 'b', 'x'))
        );
        $this->assertEquals(
            new MatcherResult(false, array(true, false), array(true, false, true)),
            $this->subject->explain($matchers, array('a', 'x', 'b'))
        );
        $this->assertEquals(
            new MatcherResult(false, array(true, false), array(true, false, false)),
            $this->subject->explain($matchers, array('a', 'x', 'x'))
        );
    }

    public function testExplainWithWildcardMaximumArguments()
    {
        $matchers = array(
            $this->matcherFactory->equalTo('a'),
            new WildcardMatcher($this->matcherFactory->equalTo('b'), 0, 1),
        );

        $this->assertEquals(
            new MatcherResult(true, array(true, true), array(true)),
            $this->subject->explain($matchers, array('a'))
        );
        $this->assertEquals(
            new MatcherResult(true, array(true, true), array(true, true)),
            $this->subject->explain($matchers, array('a', 'b'))
        );
        $this->assertEquals(
            new MatcherResult(false, array(true, true), array(true, true, false)),
            $this->subject->explain($matchers, array('a', 'b', 'b'))
        );
        $this->assertEquals(
            new MatcherResult(false, array(true, true), array(true, false)),
            $this->subject->explain($matchers, array('a', 'x'))
        );
        $this->assertEquals(
            new MatcherResult(false, array(true, true), array(true, true, false)),
            $this->subject->explain($matchers, array('a', 'b', 'x'))
        );
        $this->assertEquals(
            new MatcherResult(false, array(true, true), array(true, false, false)),
            $this->subject->explain($matchers, array('a', 'x', 'b'))
        );
        $this->assertEquals(
            new MatcherResult(false, array(true, true), array(true, false, false)),
            $this->subject->explain($matchers, array('a', 'x', 'x'))
        );
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
