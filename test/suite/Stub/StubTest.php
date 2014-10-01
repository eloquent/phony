<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Stub;

use Eloquent\Phony\Integration\Phpunit\PhpunitMatcherDriver;
use Eloquent\Phony\Matcher\EqualToMatcher;
use Eloquent\Phony\Matcher\Factory\MatcherFactory;
use Eloquent\Phony\Matcher\Verification\MatcherVerifier;
use Eloquent\Phony\Matcher\WildcardMatcher;
use PHPUnit_Framework_TestCase;

class StubTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->matcherFactory = new MatcherFactory(array(new PhpunitMatcherDriver()));
        $this->matcherVerifier = new MatcherVerifier();
        $this->subject = new Stub($this->matcherFactory, $this->matcherVerifier);

        $this->wildcard = array(WildcardMatcher::instance());
        $this->callbackA = function () { return 'valueA'; };
        $this->callbackB = function () { return 'valueB'; };
        $this->callbackC = function () { return 'valueC'; };
        $this->callbackD = function () { return 'valueD'; };
        $this->callbackE = function () { return 'valueE'; };
        $this->callbackF = function () { return 'valueF'; };
    }

    public function testConstructor()
    {
        $this->assertSame($this->matcherFactory, $this->subject->matcherFactory());
        $this->assertSame($this->matcherVerifier, $this->subject->matcherVerifier());
        $this->assertSame(array(), $this->subject->rules());
        $this->assertEquals($this->wildcard, $this->subject->matchers());
    }

    public function testConstructorDefaults()
    {
        $this->subject = new Stub();

        $this->assertSame(MatcherFactory::instance(), $this->subject->matcherFactory());
        $this->assertSame(MatcherVerifier::instance(), $this->subject->matcherVerifier());
    }

    public function testWith()
    {
        $expected = array(new EqualToMatcher('valueA'), new EqualToMatcher('valueB'), WildcardMatcher::instance());

        $this->assertSame($this->subject, $this->subject->with('valueA', new EqualToMatcher('valueB')));
        $this->assertEquals($expected, $this->subject->matchers());
    }

    public function testWithExactly()
    {
        $expected = array(new EqualToMatcher('valueA'), new EqualToMatcher('valueB'));

        $this->assertSame($this->subject, $this->subject->withExactly('valueA', new EqualToMatcher('valueB')));
        $this->assertEquals($expected, $this->subject->matchers());
    }

    public function testDoes()
    {
        $expected = array(
            array($this->wildcard, array($this->callbackA, $this->callbackB)),
        );

        $this->assertSame($this->subject, $this->subject->does($this->callbackA, $this->callbackB));
        $this->assertEquals($expected, $this->subject->rules());
        $this->assertSame('valueA', call_user_func($this->subject));
        $this->assertSame('valueB', call_user_func($this->subject));
        $this->assertSame('valueB', call_user_func($this->subject));
    }

    public function testReturns()
    {
        $expected = array(
            array($this->wildcard, array($this->callbackA, $this->callbackB)),
        );

        $this->assertSame($this->subject, $this->subject->returns('valueA', 'valueB'));
        $this->assertEquals($expected, $this->subject->rules());
        $this->assertSame('valueA', call_user_func($this->subject));
        $this->assertSame('valueB', call_user_func($this->subject));
        $this->assertSame('valueB', call_user_func($this->subject));
    }

    public function testWithMultipleRules()
    {
        $expected = array(
            array(
                array(new EqualToMatcher('argumentB'), WildcardMatcher::instance()),
                array($this->callbackE, $this->callbackF),
            ),
            array(
                array(new EqualToMatcher('argumentA'), WildcardMatcher::instance()),
                array($this->callbackB, $this->callbackC, $this->callbackD),
            ),
            array(
                $this->wildcard,
                array($this->callbackA),
            ),
        );

        $this->assertSame(
            $this->subject,
            $this->subject
                ->returns('valueA')
                ->with('argumentA')->returns('valueB', 'valueC')->returns('valueD')
                ->with('argumentB')->returns('valueE', 'valueF')
        );
        $this->assertEquals($expected, $this->subject->rules());
        $this->assertSame('valueA', call_user_func($this->subject));
        $this->assertSame('valueA', call_user_func($this->subject));
        $this->assertSame('valueB', call_user_func($this->subject, 'argumentA'));
        $this->assertSame('valueC', call_user_func($this->subject, 'argumentA'));
        $this->assertSame('valueD', call_user_func($this->subject, 'argumentA'));
        $this->assertSame('valueD', call_user_func($this->subject, 'argumentA'));
        $this->assertSame('valueE', call_user_func($this->subject, 'argumentB'));
        $this->assertSame('valueF', call_user_func($this->subject, 'argumentB'));
        $this->assertSame('valueF', call_user_func($this->subject, 'argumentB'));
        $this->assertSame('valueA', call_user_func($this->subject));
        $this->assertSame('valueD', call_user_func($this->subject, 'argumentA'));
        $this->assertSame('valueF', call_user_func($this->subject, 'argumentB'));
    }

    public function testInvokeWithNoRules()
    {
        $this->assertNull(call_user_func($this->subject));
    }
}
