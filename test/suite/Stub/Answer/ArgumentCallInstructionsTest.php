<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Stub\Answer;

use PHPUnit_Framework_TestCase;

class ArgumentCallInstructionsTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->index = 2;
        $this->arguments = array('a', 'b');
        $this->prefixSelf = true;
        $this->suffixArgumentsArray = true;
        $this->suffixArguments = false;
        $this->subject = new ArgumentCallInstructions(
            $this->index,
            $this->arguments,
            $this->prefixSelf,
            $this->suffixArgumentsArray,
            $this->suffixArguments
        );
    }

    public function testConstructor()
    {
        $this->assertSame($this->index, $this->subject->index());
        $this->assertSame($this->arguments, $this->subject->arguments());
        $this->assertSame($this->prefixSelf, $this->subject->prefixSelf());
        $this->assertSame($this->suffixArgumentsArray, $this->subject->suffixArgumentsArray());
        $this->assertSame($this->suffixArguments, $this->subject->suffixArguments());
    }

    public function testConstructorDefaults()
    {
        $this->subject = new ArgumentCallInstructions($this->index);

        $this->assertSame(array(), $this->subject->arguments());
        $this->assertFalse($this->subject->prefixSelf());
        $this->assertFalse($this->subject->suffixArgumentsArray());
        $this->assertTrue($this->subject->suffixArguments());
    }

    public function callbackData()
    {
        //                                   index arguments        expected
        return array(
            'First argument'        => array(0,    array('a', 'b'), 'a'),
            'Second argument'       => array(1,    array('a', 'b'), 'b'),
            'Last argument'         => array(-1,   array('a', 'b'), 'b'),
            'Penultimate argument'  => array(-2,   array('a', 'b'), 'a'),
            'Undefined'             => array(2,    array('a', 'b'), null),
            'Undefined negative'    => array(-3,   array('a', 'b'), null),
            'No arguments'          => array(0,    null,            null),
            'No arguments negative' => array(-1,   null,            null),
        );
    }

    /**
     * @dataProvider callbackData
     */
    public function testCallback($index, $arguments, $expected)
    {
        $this->subject = new ArgumentCallInstructions($index);

        $this->assertSame($expected, $this->subject->callback($arguments));
    }

    public function finalArgumentsData()
    {
        $self = (object) array();

        //                                 arguments        prefixSelf suffixArray suffix self   incoming         expected
        return array(
            'No suffix or prefix' => array(array('a', 'b'), false,     false,      false, $self, array('c', 'd'), array('a', 'b')),
            'Prefix self'         => array(array('a', 'b'), true,      false,      false, $self, array('c', 'd'), array($self, 'a', 'b')),
            'Suffix array'        => array(array('a', 'b'), false,     true,       false, $self, array('c', 'd'), array('a', 'b', array('c', 'd'))),
            'Suffix normal'       => array(array('a', 'b'), false,     false,      true,  $self, array('c', 'd'), array('a', 'b', 'c', 'd')),
            'One with the lot'    => array(array('a', 'b'), true,      true,       true,  $self, array('c', 'd'), array($self, 'a', 'b', array('c', 'd'), 'c', 'd')),
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
        $this->subject = new ArgumentCallInstructions(
            $this->index,
            $arguments,
            $prefixSelf,
            $suffixArray,
            $suffix
        );

        $this->assertSame($expected, $this->subject->finalArguments($self, $incoming));
    }
}
