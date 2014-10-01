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

use Eloquent\Phony\Matcher\EqualToMatcher;
use Eloquent\Phony\Matcher\Factory\MatcherFactory;
use Eloquent\Phony\Matcher\Verification\MatcherVerifier;
use Eloquent\Phony\Matcher\WildcardMatcher;
use PHPUnit_Framework_TestCase;

class StubTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->thisValue = (object) array();
        $this->matcherFactory = new MatcherFactory();
        $this->matcherVerifier = new MatcherVerifier();
        $this->subject = new Stub($this->thisValue, $this->matcherFactory, $this->matcherVerifier);

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
        $this->assertSame($this->thisValue, $this->subject->thisValue());
        $this->assertSame($this->matcherFactory, $this->subject->matcherFactory());
        $this->assertSame($this->matcherVerifier, $this->subject->matcherVerifier());
    }

    public function testConstructorDefaults()
    {
        $this->subject = new Stub();

        $this->assertSame($this->subject, $this->subject->thisValue());
        $this->assertSame(MatcherFactory::instance(), $this->subject->matcherFactory());
        $this->assertSame(MatcherVerifier::instance(), $this->subject->matcherVerifier());
    }

    public function testSetThisValue()
    {
        $this->subject->setThisValue(null);

        $this->assertSame($this->subject, $this->subject->thisValue());

        $this->thisValue = (object) array();
        $this->subject->setThisValue($this->thisValue);

        $this->assertSame($this->thisValue, $this->subject->thisValue());
    }

    public function testWith()
    {
        $this->assertSame(
            $this->subject,
            $this->subject
                ->with('argumentA', new EqualToMatcher('argumentB'))
                ->returns('value')
        );
        $this->assertSame('value', call_user_func($this->subject, 'argumentA', 'argumentB'));
        $this->assertSame('value', call_user_func($this->subject, 'argumentA', 'argumentB', 'argumentC'));
        $this->assertNull(call_user_func($this->subject));
    }

    public function testWithExactly()
    {
        $this->assertSame(
            $this->subject,
            $this->subject
                ->withExactly('argumentA', new EqualToMatcher('argumentB'))
                ->returns('value')
        );
        $this->assertSame('value', call_user_func($this->subject, 'argumentA', 'argumentB'));
        $this->assertSame('value', call_user_func($this->subject, 'argumentA', 'argumentB'));
        $this->assertNull(call_user_func($this->subject, 'argumentA', 'argumentB', 'argumentC'));
        $this->assertNull(call_user_func($this->subject));
    }

    public function testDoes()
    {
        $this->assertSame($this->subject, $this->subject->does($this->callbackA, $this->callbackB));
        $this->assertSame('valueA', call_user_func($this->subject));
        $this->assertSame('valueB', call_user_func($this->subject));
        $this->assertSame('valueB', call_user_func($this->subject));
    }

    public function testReturns()
    {
        $this->assertSame($this->subject, $this->subject->returns('valueA', 'valueB'));
        $this->assertSame('valueA', call_user_func($this->subject));
        $this->assertSame('valueB', call_user_func($this->subject));
        $this->assertSame('valueB', call_user_func($this->subject));
    }

    public function testReturnsArgument()
    {
        $this->assertSame($this->subject, $this->subject->returnsArgument());
        $this->assertSame('argumentA', call_user_func($this->subject, 'argumentA'));
        $this->assertSame('argumentB', call_user_func($this->subject, 'argumentB'));
        $this->assertNull(call_user_func($this->subject));
        $this->assertSame($this->subject, $this->subject->with()->returnsArgument(1));
        $this->assertSame('argumentB', call_user_func($this->subject, 'argumentA', 'argumentB', 'argumentC'));
        $this->assertSame('argumentC', call_user_func($this->subject, 'argumentB', 'argumentC', 'argumentD'));
        $this->assertNull(call_user_func($this->subject, 'argumentA'));
        $this->assertSame($this->subject, $this->subject->with()->returnsArgument(-1));
        $this->assertSame('argumentC', call_user_func($this->subject, 'argumentA', 'argumentB', 'argumentC'));
        $this->assertSame('argumentD', call_user_func($this->subject, 'argumentB', 'argumentC', 'argumentD'));
        $this->assertNull(call_user_func($this->subject));
    }

    public function testReturnsThis()
    {
        $this->assertSame($this->subject, $this->subject->returnsThis());
        $this->assertSame($this->thisValue, call_user_func($this->subject));

        $this->subject->setThisValue(null);

        $this->assertSame($this->subject, call_user_func($this->subject));
    }

    public function testMultipleRules()
    {
        $this->assertSame(
            $this->subject,
            $this->subject
                ->returns('valueA')
                ->with('argumentA')->returns('valueB', 'valueC')->returns('valueD')
                ->with('argumentB')->returns('valueE', 'valueF')
        );
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
        $this->assertSame(
            $this->subject,
            $this->subject
                ->with()->returns('valueB')
                ->with('argumentA')->returns('valueC')
                ->with('argumentB')->returns('valueE')
        );
        $this->assertSame('valueB', call_user_func($this->subject));
        $this->assertSame('valueB', call_user_func($this->subject));
        $this->assertSame('valueC', call_user_func($this->subject, 'argumentA'));
        $this->assertSame('valueC', call_user_func($this->subject, 'argumentA'));
        $this->assertSame('valueE', call_user_func($this->subject, 'argumentB'));
        $this->assertSame('valueE', call_user_func($this->subject, 'argumentB'));
    }

    public function testInvokeWithNoRules()
    {
        $this->assertNull(call_user_func($this->subject));
    }
}
