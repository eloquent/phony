<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Spy;

use Eloquent\Phony\Call\Call;
use Eloquent\Phony\Call\Factory\CallVerifierFactory;
use Eloquent\Phony\Clock\TestClock;
use Eloquent\Phony\Matcher\Factory\MatcherFactory;
use Eloquent\Phony\Matcher\Verification\MatcherVerifier;
use Exception;
use PHPUnit_Framework_TestCase;
use ReflectionClass;
use ReflectionFunction;
use RuntimeException;

class SpyVerifierTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->spySubject = function () {
            return '= ' .implode(', ', func_get_args());
        };
        $this->clock = new TestClock();
        $this->spy = new Spy($this->spySubject, null, $this->clock);
        $this->matcherFactory = new MatcherFactory();
        $this->matcherVerifier = new MatcherVerifier();
        $this->callVerifierFactory = new CallVerifierFactory();
        $this->subject = new SpyVerifier(
            $this->spy,
            $this->matcherFactory,
            $this->matcherVerifier,
            $this->callVerifierFactory
        );

        $this->returnValueA = 'returnValueA';
        $this->returnValueB = 'returnValueB';
        $this->exceptionA = new RuntimeException('You done goofed.');
        $this->exceptionB = new RuntimeException('Consequences will never be the same.');
        $this->thisValueA = (object) array();
        $this->thisValueB = (object) array();
        $this->callA = new Call(array('argumentA', 'argumentB', 'argumentC'), null, 0, 1.11, 2.22);
        $this->callB = new Call(array(), null, 1, 3.33, 4.44);
        $this->callC = new Call(array(), $this->returnValueA, 2, 5.55, 6.66, $this->exceptionA, $this->thisValueA);
        $this->callD = new Call(array(), $this->returnValueB, 3, 7.77, 8.88, $this->exceptionB, $this->thisValueB);
        $this->calls = array($this->callA, $this->callB, $this->callC, $this->callD);
    }

    public function testConstructor()
    {
        $this->assertSame($this->spy, $this->subject->spy());
        $this->assertSame($this->matcherFactory, $this->subject->matcherFactory());
        $this->assertSame($this->matcherVerifier, $this->subject->matcherVerifier());
        $this->assertSame($this->callVerifierFactory, $this->subject->callVerifierFactory());
    }

    public function testConstructorDefaults()
    {
        $this->subject = new SpyVerifier();

        $this->assertEquals(new Spy(), $this->subject->spy());
        $this->assertEquals($this->matcherFactory, $this->subject->matcherFactory());
        $this->assertSame(MatcherVerifier::instance(), $this->subject->matcherVerifier());
        $this->assertSame(CallVerifierFactory::instance(), $this->subject->callVerifierFactory());
    }

    public function testProxyMethods()
    {
        $this->assertTrue($this->subject->hasSubject());
        $this->assertSame($this->spySubject, $this->subject->subject());
        $this->assertSame(array(), $this->subject->calls());
    }

    public function testSetCalls()
    {
        $this->subject->setCalls($this->calls);

        $this->assertSame($this->calls, $this->subject->spy()->calls());
    }

    public function testAddCall()
    {
        $this->subject->addCall($this->callA);

        $this->assertSame(array($this->callA), $this->subject->spy()->calls());

        $this->subject->addCall($this->callB);

        $this->assertSame(array($this->callA, $this->callB), $this->subject->spy()->calls());
    }

    public function testInvoke()
    {
        $spy = $this->subject;
        $spy('argumentA');
        $spy('argumentB', 'argumentC');
        $thisValue = $this->thisValue($this->spySubject);
        $expected = array(
            new Call(array('argumentA'), '= argumentA', 0, 0.123, 1.123, null, $thisValue),
            new Call(array('argumentB', 'argumentC'), '= argumentB, argumentC', 1, 2.123, 3.123, null, $thisValue),
        );

        $this->assertEquals($expected, $spy->spy()->calls());
    }

    public function testCallCount()
    {
        $this->assertSame(0, $this->subject->callCount());

        $this->subject->setCalls($this->calls);

        $this->assertSame(4, $this->subject->callCount());
    }

    public function testCallAt()
    {
        $this->subject->setCalls($this->calls);

        $this->assertSame($this->callA, $this->subject->callAt(0));
        $this->assertSame($this->callB, $this->subject->callAt(1));
    }

    public function testCallAtFailureUndefined()
    {
        $this->setExpectedException('Eloquent\Phony\Call\Exception\UndefinedCallException');
        $this->subject->callAt(0);
    }

    public function testFirstCall()
    {
        $this->subject->setCalls($this->calls);

        $this->assertSame($this->callA, $this->subject->firstCall());
    }

    public function testFirstCallFailureUndefined()
    {
        $this->setExpectedException('Eloquent\Phony\Call\Exception\UndefinedCallException');
        $this->subject->firstCall();
    }

    public function testLastCall()
    {
        $this->subject->setCalls($this->calls);

        $this->assertSame($this->callD, $this->subject->lastCall());
    }

    public function testLastCallFailureUndefined()
    {
        $this->setExpectedException('Eloquent\Phony\Call\Exception\UndefinedCallException');
        $this->subject->lastCall();
    }

    public function testCalled()
    {
        $this->assertFalse($this->subject->called());

        $this->subject->setCalls($this->calls);

        $this->assertTrue($this->subject->called());
    }

    public function testCalledOnce()
    {
        $this->assertFalse($this->subject->calledOnce());

        $this->subject->addCall($this->callA);

        $this->assertTrue($this->subject->calledOnce());

        $this->subject->addCall($this->callB);

        $this->assertFalse($this->subject->calledOnce());
    }

    public function testCalledTimes()
    {
        $this->assertTrue($this->subject->calledTimes(0));
        $this->assertFalse($this->subject->calledTimes(4));

        $this->subject->setCalls($this->calls);

        $this->assertFalse($this->subject->calledTimes(0));
        $this->assertTrue($this->subject->calledTimes(4));
    }

    public function testCalledBefore()
    {
        $spyA = new Spy();
        $spyA->setCalls(array($this->callA, $this->callC));
        $spyB = new Spy();
        $spyB->setCalls(array($this->callA));
        $spyC = new Spy();

        $this->assertFalse($this->subject->calledBefore($spyA));
        $this->assertFalse($this->subject->calledBefore($spyB));
        $this->assertFalse($this->subject->calledBefore($spyC));

        $this->subject->setCalls(array($this->callB, $this->callD));

        $this->assertTrue($this->subject->calledBefore($spyA));
        $this->assertFalse($this->subject->calledBefore($spyB));
        $this->assertFalse($this->subject->calledBefore($spyC));
    }

    public function testCalledAfter()
    {
        $spyA = new Spy();
        $spyA->setCalls(array($this->callB, $this->callD));
        $spyB = new Spy();
        $spyB->setCalls(array($this->callD));
        $spyC = new Spy();

        $this->assertFalse($this->subject->calledAfter($spyA));
        $this->assertFalse($this->subject->calledAfter($spyB));
        $this->assertFalse($this->subject->calledAfter($spyC));

        $this->subject->setCalls(array($this->callA, $this->callC));

        $this->assertTrue($this->subject->calledAfter($spyA));
        $this->assertFalse($this->subject->calledAfter($spyB));
        $this->assertFalse($this->subject->calledAfter($spyC));
    }

    public function calledWithData()
    {
        //                                    arguments                                                  calledWith calledWithExactly
        return array(
            'Exact arguments'        => array(array('argumentA', 'argumentB', 'argumentC'),              true,      true),
            'First arguments'        => array(array('argumentA', 'argumentB'),                           true,      false),
            'Single argument'        => array(array('argumentA'),                                        true,      false),
            'Last arguments'         => array(array('argumentB', 'argumentC'),                           false,     false),
            'Last argument'          => array(array('argumentC'),                                        false,     false),
            'Extra arguments'        => array(array('argumentA', 'argumentB', 'argumentC', 'argumentD'), false,     false),
            'First argument differs' => array(array('argumentD', 'argumentB', 'argumentC'),              false,     false),
            'Last argument differs'  => array(array('argumentA', 'argumentB', 'argumentD'),              false,     false),
            'Unused argument'        => array(array('argumentD'),                                        false,     false),
        );
    }

    /**
     * @dataProvider calledWithData
     */
    public function testCalledWith(array $arguments, $calledWith, $calledWithExactly)
    {
        $this->subject->setCalls($this->calls);
        $matchers = $this->matcherFactory->adaptAll($arguments);

        $this->assertSame($calledWith, call_user_func_array(array($this->subject, 'calledWith'), $arguments));
        $this->assertSame($calledWith, call_user_func_array(array($this->subject, 'calledWith'), $matchers));
    }

    public function testCalledWithWithEmptyArguments()
    {
        $this->subject->setCalls($this->calls);

        $this->assertTrue($this->subject->calledWith());
    }

    public function testCalledWithWithNoCalls()
    {
        $this->assertFalse($this->subject->calledWith());
    }

    /**
     * @dataProvider calledWithData
     */
    public function testAlwaysCalledWith(array $arguments, $calledWith, $calledWithExactly)
    {
        $this->subject->setCalls(array($this->callA, $this->callA));
        $matchers = $this->matcherFactory->adaptAll($arguments);

        $this->assertSame($calledWith, call_user_func_array(array($this->subject, 'alwaysCalledWith'), $arguments));
        $this->assertSame($calledWith, call_user_func_array(array($this->subject, 'alwaysCalledWith'), $matchers));
    }

    /**
     * @dataProvider calledWithData
     */
    public function testAlwaysCalledWithWithDifferingCalls(array $arguments, $calledWith, $calledWithExactly)
    {
        $this->subject->setCalls(array($this->callA, $this->callB));
        $matchers = $this->matcherFactory->adaptAll($arguments);

        $this->assertFalse(call_user_func_array(array($this->subject, 'alwaysCalledWith'), $arguments));
        $this->assertFalse(call_user_func_array(array($this->subject, 'alwaysCalledWith'), $matchers));
    }

    public function testAlwaysCalledWithWithEmptyArguments()
    {
        $this->subject->setCalls($this->calls);

        $this->assertTrue($this->subject->alwaysCalledWith());
    }

    public function testAlwaysCalledWithWithNoCalls()
    {
        $this->assertFalse($this->subject->alwaysCalledWith());
    }

    /**
     * @dataProvider calledWithData
     */
    public function testCalledWithExactly(array $arguments, $calledWith, $calledWithExactly)
    {
        $this->subject->setCalls($this->calls);
        $matchers = $this->matcherFactory->adaptAll($arguments);

        $this->assertSame(
            $calledWithExactly,
            call_user_func_array(array($this->subject, 'calledWithExactly'), $arguments)
        );
        $this->assertSame(
            $calledWithExactly,
            call_user_func_array(array($this->subject, 'calledWithExactly'), $matchers)
        );
    }

    public function testCalledWithExactlyWithEmptyArguments()
    {
        $this->subject->setCalls($this->calls);

        $this->assertTrue($this->subject->calledWithExactly());
    }

    public function testCalledWithExactlyWithNoCalls()
    {
        $this->assertFalse($this->subject->calledWithExactly());
    }

    /**
     * @dataProvider calledWithData
     */
    public function testAlwaysCalledWithExactly(array $arguments, $calledWith, $calledWithExactly)
    {
        $this->subject->setCalls(array($this->callA, $this->callA));
        $matchers = $this->matcherFactory->adaptAll($arguments);

        $this->assertSame(
            $calledWithExactly,
            call_user_func_array(array($this->subject, 'alwaysCalledWithExactly'), $arguments)
        );
        $this->assertSame(
            $calledWithExactly,
            call_user_func_array(array($this->subject, 'alwaysCalledWithExactly'), $matchers)
        );
    }

    /**
     * @dataProvider calledWithData
     */
    public function testAlwaysCalledWithExactlyWithDifferingCalls(array $arguments, $calledWith, $calledWithExactly)
    {
        $this->subject->setCalls(array($this->callA, $this->callB));
        $matchers = $this->matcherFactory->adaptAll($arguments);

        $this->assertFalse(call_user_func_array(array($this->subject, 'alwaysCalledWithExactly'), $arguments));
        $this->assertFalse(call_user_func_array(array($this->subject, 'alwaysCalledWithExactly'), $matchers));
    }

    public function testAlwaysCalledWithExactlyWithEmptyArguments()
    {
        $this->subject->setCalls($this->calls);

        $this->assertFalse($this->subject->alwaysCalledWithExactly());
    }

    public function testAlwaysCalledWithExactlyWithNoCalls()
    {
        $this->assertFalse($this->subject->alwaysCalledWithExactly());
    }

    /**
     * @dataProvider calledWithData
     */
    public function testNeverCalledWith(array $arguments, $calledWith, $calledWithExactly)
    {
        $this->subject->setCalls($this->calls);
        $matchers = $this->matcherFactory->adaptAll($arguments);

        $this->assertSame(!$calledWith, call_user_func_array(array($this->subject, 'neverCalledWith'), $arguments));
        $this->assertSame(!$calledWith, call_user_func_array(array($this->subject, 'neverCalledWith'), $matchers));
    }

    public function testNeverCalledWithWithEmptyArguments()
    {
        $this->subject->setCalls($this->calls);

        $this->assertFalse($this->subject->neverCalledWith());
    }

    public function testNeverCalledWithWithNoCalls()
    {
        $this->assertTrue($this->subject->neverCalledWith());
    }

    /**
     * @dataProvider calledWithData
     */
    public function testNeverCalledWithExactly(array $arguments, $calledWith, $calledWithExactly)
    {
        $this->subject->setCalls($this->calls);
        $matchers = $this->matcherFactory->adaptAll($arguments);

        $this->assertSame(
            !$calledWithExactly,
            call_user_func_array(array($this->subject, 'neverCalledWithExactly'), $arguments)
        );
        $this->assertSame(
            !$calledWithExactly,
            call_user_func_array(array($this->subject, 'neverCalledWithExactly'), $matchers)
        );
    }

    public function testNeverCalledWithExactlyWithEmptyArguments()
    {
        $this->subject->setCalls($this->calls);

        $this->assertFalse($this->subject->neverCalledWithExactly());
    }

    public function testNeverCalledWithExactlyWithNoCalls()
    {
        $this->assertTrue($this->subject->neverCalledWithExactly());
    }

    public function testCalledOn()
    {
        $this->assertFalse($this->subject->calledOn(null));
        $this->assertFalse($this->subject->calledOn($this->thisValueA));
        $this->assertFalse($this->subject->calledOn($this->thisValueB));

        $this->subject->setCalls($this->calls);

        $this->assertTrue($this->subject->calledOn(null));
        $this->assertTrue($this->subject->calledOn($this->thisValueA));
        $this->assertTrue($this->subject->calledOn($this->thisValueB));
        $this->assertFalse($this->subject->calledOn((object) array()));
    }

    public function testAlwaysCalledOn()
    {
        $this->assertFalse($this->subject->alwaysCalledOn(null));
        $this->assertFalse($this->subject->alwaysCalledOn($this->thisValueA));
        $this->assertFalse($this->subject->alwaysCalledOn($this->thisValueB));
        $this->assertFalse($this->subject->alwaysCalledOn((object) array()));

        $this->subject->setCalls($this->calls);

        $this->assertFalse($this->subject->alwaysCalledOn(null));
        $this->assertFalse($this->subject->alwaysCalledOn($this->thisValueA));
        $this->assertFalse($this->subject->alwaysCalledOn($this->thisValueB));
        $this->assertFalse($this->subject->alwaysCalledOn((object) array()));

        $this->subject->setCalls(array($this->callC, $this->callC));

        $this->assertTrue($this->subject->alwaysCalledOn($this->thisValueA));
        $this->assertFalse($this->subject->alwaysCalledOn(null));
        $this->assertFalse($this->subject->alwaysCalledOn($this->thisValueB));
        $this->assertFalse($this->subject->alwaysCalledOn((object) array()));
    }

    public function testReturned()
    {
        $this->assertFalse($this->subject->returned(null));
        $this->assertFalse($this->subject->returned($this->returnValueA));
        $this->assertFalse($this->subject->returned($this->returnValueB));

        $this->subject->setCalls($this->calls);

        $this->assertTrue($this->subject->returned(null));
        $this->assertTrue($this->subject->returned($this->returnValueA));
        $this->assertTrue($this->subject->returned($this->returnValueB));
        $this->assertFalse($this->subject->returned('anotherValue'));
    }

    public function testAlwaysReturned()
    {
        $this->assertFalse($this->subject->alwaysReturned(null));
        $this->assertFalse($this->subject->alwaysReturned($this->returnValueA));
        $this->assertFalse($this->subject->alwaysReturned($this->returnValueB));
        $this->assertFalse($this->subject->alwaysReturned('anotherValue'));

        $this->subject->setCalls($this->calls);

        $this->assertFalse($this->subject->alwaysReturned(null));
        $this->assertFalse($this->subject->alwaysReturned($this->returnValueA));
        $this->assertFalse($this->subject->alwaysReturned($this->returnValueB));
        $this->assertFalse($this->subject->alwaysReturned('anotherValue'));

        $this->subject->setCalls(array($this->callC, $this->callC));

        $this->assertTrue($this->subject->alwaysReturned($this->returnValueA));
        $this->assertFalse($this->subject->alwaysReturned(null));
        $this->assertFalse($this->subject->alwaysReturned($this->returnValueB));
        $this->assertFalse($this->subject->alwaysReturned('anotherValue'));
    }

    public function testThrew()
    {
        $this->assertFalse($this->subject->threw());
        $this->assertFalse($this->subject->threw('Exception'));
        $this->assertFalse($this->subject->threw('RuntimeException'));
        $this->assertFalse($this->subject->threw($this->exceptionA));
        $this->assertFalse($this->subject->threw($this->exceptionB));
        $this->assertFalse($this->subject->threw('InvalidArgumentException'));
        $this->assertFalse($this->subject->threw(new Exception()));
        $this->assertFalse($this->subject->threw(new RuntimeException()));

        $this->subject->setCalls($this->calls);

        $this->assertTrue($this->subject->threw());
        $this->assertTrue($this->subject->threw('Exception'));
        $this->assertTrue($this->subject->threw('RuntimeException'));
        $this->assertTrue($this->subject->threw($this->exceptionA));
        $this->assertTrue($this->subject->threw($this->exceptionB));
        $this->assertFalse($this->subject->threw('InvalidArgumentException'));
        $this->assertFalse($this->subject->threw(new Exception()));
        $this->assertFalse($this->subject->threw(new RuntimeException()));
    }

    public function testAlwaysThrew()
    {
        $this->assertFalse($this->subject->alwaysThrew());
        $this->assertFalse($this->subject->alwaysThrew('Exception'));
        $this->assertFalse($this->subject->alwaysThrew('RuntimeException'));
        $this->assertFalse($this->subject->alwaysThrew($this->exceptionA));
        $this->assertFalse($this->subject->alwaysThrew($this->exceptionB));
        $this->assertFalse($this->subject->alwaysThrew('InvalidArgumentException'));
        $this->assertFalse($this->subject->alwaysThrew(new Exception()));
        $this->assertFalse($this->subject->alwaysThrew(new RuntimeException()));

        $this->subject->setCalls($this->calls);

        $this->assertFalse($this->subject->alwaysThrew());
        $this->assertFalse($this->subject->alwaysThrew('Exception'));
        $this->assertFalse($this->subject->alwaysThrew('RuntimeException'));
        $this->assertFalse($this->subject->alwaysThrew($this->exceptionA));
        $this->assertFalse($this->subject->alwaysThrew($this->exceptionB));
        $this->assertFalse($this->subject->alwaysThrew('InvalidArgumentException'));
        $this->assertFalse($this->subject->alwaysThrew(new Exception()));
        $this->assertFalse($this->subject->alwaysThrew(new RuntimeException()));

        $this->subject->setCalls(array($this->callC, $this->callC));

        $this->assertTrue($this->subject->alwaysThrew());
        $this->assertTrue($this->subject->alwaysThrew('Exception'));
        $this->assertTrue($this->subject->alwaysThrew('RuntimeException'));
        $this->assertTrue($this->subject->alwaysThrew($this->exceptionA));
        $this->assertFalse($this->subject->alwaysThrew($this->exceptionB));
        $this->assertFalse($this->subject->alwaysThrew('InvalidArgumentException'));
        $this->assertFalse($this->subject->alwaysThrew(new Exception()));
        $this->assertFalse($this->subject->alwaysThrew(new RuntimeException()));
    }

    protected function thisValue($closure)
    {
        $reflectorReflector = new ReflectionClass('ReflectionFunction');
        if (!$reflectorReflector->hasMethod('getClosureThis')) {
            return null;
        }

        $reflector = new ReflectionFunction($closure);

        return $reflector->getClosureThis();
    }
}
