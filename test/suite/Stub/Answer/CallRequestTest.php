<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2017 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Stub\Answer;

use Eloquent\Phony\Call\Arguments;
use Eloquent\Phony\Phony;
use PHPUnit\Framework\TestCase;

class CallRequestTest extends TestCase
{
    protected function setUp()
    {
        $this->callback = 'implode';
        $this->arguments = new Arguments(array('a', 'b'));
        $this->prefixSelf = true;
        $this->suffixArgumentsObject = true;
        $this->suffixArguments = false;
        $this->subject = new CallRequest(
            $this->callback,
            $this->arguments,
            $this->prefixSelf,
            $this->suffixArgumentsObject,
            $this->suffixArguments
        );
    }

    public function testConstructor()
    {
        $this->assertSame($this->callback, $this->subject->callback());
        $this->assertSame($this->arguments, $this->subject->arguments());
        $this->assertSame($this->prefixSelf, $this->subject->prefixSelf());
        $this->assertSame($this->suffixArgumentsObject, $this->subject->suffixArgumentsObject());
        $this->assertSame($this->suffixArguments, $this->subject->suffixArguments());
    }

    public function testConstructorWithInstanceHandles()
    {
        $handle = Phony::mock();
        $this->arguments = new Arguments(array($handle));
        $this->subject = new CallRequest($this->callback, $this->arguments, false, false, false);

        $this->assertSame($handle->get(), $this->subject->arguments()->get(0));
    }

    public function finalArgumentsData()
    {
        $self = (object) array();

        //                                 arguments        prefixSelf suffixArray suffix self   incoming         expected
        return array(
            'No suffix or prefix' => array(array('a', 'b'), false,     false,      false, $self, array('c', 'd'), array('a', 'b')),
            'Prefix self'         => array(array('a', 'b'), true,      false,      false, $self, array('c', 'd'), array($self, 'a', 'b')),
            'Suffix array'        => array(array('a', 'b'), false,     true,       false, $self, array('c', 'd'), array('a', 'b', new Arguments(array('c', 'd')))),
            'Suffix normal'       => array(array('a', 'b'), false,     false,      true,  $self, array('c', 'd'), array('a', 'b', 'c', 'd')),
            'One with the lot'    => array(array('a', 'b'), true,      true,       true,  $self, array('c', 'd'), array($self, 'a', 'b', new Arguments(array('c', 'd')), 'c', 'd')),
        );
    }

    /**
     * @dataProvider finalArgumentsData
     */
    public function testFinalArguments(
        $arguments,
        $prefixSelf,
        $suffixArray,
        $suffix,
        $self,
        $incoming,
        $expected
    ) {
        $this->subject =
            new CallRequest($this->callback, new Arguments($arguments), $prefixSelf, $suffixArray, $suffix);

        $this->assertEquals($expected, $this->subject->finalArguments($self, new Arguments($incoming))->all());
    }

    public function testFinalArgumentsWithReferenceParameters()
    {
        $a = null;
        $b = null;
        $c = null;
        $d = null;
        $arguments = new Arguments(array(&$a, &$b));
        $incoming = new Arguments(array(&$c, &$d));
        $this->subject = new CallRequest($this->callback, $arguments, false, false, true);
        $finalArguments = $this->subject->finalArguments(null, $incoming)->all();
        $finalArguments[0] = 'a';
        $finalArguments[1] = 'b';
        $finalArguments[2] = 'c';
        $finalArguments[3] = 'd';

        $this->assertSame('a', $a);
        $this->assertSame('b', $b);
        $this->assertSame('c', $c);
        $this->assertSame('d', $d);
        $this->assertSame(array('a', 'b'), $arguments->all());
        $this->assertSame(array('c', 'd'), $incoming->all());
        $this->assertSame(array('a', 'b', 'c', 'd'), $finalArguments);
    }

    public function testFinalArgumentsWithReferenceParametersArray()
    {
        $a = null;
        $b = null;
        $c = null;
        $d = null;
        $arguments = new Arguments(array(&$a, &$b));
        $incoming = new Arguments(array(&$c, &$d));
        $this->subject = new CallRequest($this->callback, $arguments, false, true, false);
        $finalArguments = $this->subject->finalArguments(null, $incoming)->all();
        $finalArguments[2] = $finalArguments[2]->all();
        $finalArguments[0] = 'a';
        $finalArguments[1] = 'b';
        $finalArguments[2][0] = 'c';
        $finalArguments[2][1] = 'd';

        $this->assertSame('a', $a);
        $this->assertSame('b', $b);
        $this->assertSame('c', $c);
        $this->assertSame('d', $d);
        $this->assertSame(array('a', 'b'), $arguments->all());
        $this->assertSame(array('c', 'd'), $incoming->all());
        $this->assertSame(array('a', 'b', array('c', 'd')), $finalArguments);
    }
}
