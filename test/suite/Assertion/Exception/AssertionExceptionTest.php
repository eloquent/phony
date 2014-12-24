<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Assertion\Exception;

use Exception;
use PHPUnit_Framework_TestCase;

class AssertionExceptionTest extends PHPUnit_Framework_TestCase
{
    public function testException()
    {
        $message = 'message';
        $cause = new Exception();
        $exception = new AssertionException($message, $cause);

        $this->assertSame($message, $exception->getMessage());
        $this->assertSame(0, $exception->getCode());
        $this->assertSame($cause, $exception->getPrevious());
    }

    public function tracePhonyCallData()
    {
        return array(
            'Method' => array(
                array(
                    array(
                        'file' => '/path/to/file/a',
                        'line' => 111,
                        'function' => 'methodA',
                        'class' =>  'Vendor\Package\ClassA'
                    ),
                    array(
                        'file' => '/path/to/file/b',
                        'line' => 222,
                        'function' => 'methodB',
                        'class' =>  'Vendor\Package\ClassB'
                    ),
                    array(
                        'file' => '/path/to/file/c',
                        'line' => 333,
                        'function' => 'methodC',
                        'class' => 'ClassC',
                    ),
                ),
                'Vendor\Package\\',
                array(
                    'file' => '/path/to/file/b',
                    'line' => 222,
                    'function' => 'methodB',
                    'class' =>  'Vendor\Package\ClassB'
                ),
            ),

            'Function' => array(
                array(
                    array(
                        'file' => '/path/to/file/a',
                        'line' => 111,
                        'function' => 'methodA',
                        'class' =>  'Vendor\Package\ClassA'
                    ),
                    array(
                        'file' => '/path/to/file/b',
                        'line' => 222,
                        'function' => 'Vendor\Package\functionB',
                    ),
                    array(
                        'file' => '/path/to/file/c',
                        'line' => 333,
                        'function' => 'functionC',
                    ),
                ),
                'Vendor\Package\\',
                array(
                    'file' => '/path/to/file/b',
                    'line' => 222,
                    'function' => 'Vendor\Package\functionB',
                ),
            ),

            'Default prefix' => array(
                array(
                    array(
                        'file' => '/path/to/file/a',
                        'line' => 111,
                        'function' => 'methodA',
                        'class' =>  'Eloquent\Phony\ClassA'
                    ),
                    array(
                        'file' => '/path/to/file/b',
                        'line' => 222,
                        'function' => 'Eloquent\Phony\functionB',
                    ),
                    array(
                        'file' => '/path/to/file/c',
                        'line' => 333,
                        'function' => 'functionC',
                    ),
                ),
                null,
                array(
                    'file' => '/path/to/file/b',
                    'line' => 222,
                    'function' => 'Eloquent\Phony\functionB',
                ),
            ),

            'No external calls' => array(
                array(
                    array(
                        'file' => '/path/to/file/a',
                        'line' => 111,
                        'function' => 'methodA',
                        'class' =>  'Vendor\Package\ClassA'
                    ),
                    array(
                        'file' => '/path/to/file/b',
                        'line' => 222,
                        'function' => 'Vendor\Package\functionB',
                    ),
                ),
                'Vendor\Package\\',
                array(
                    'file' => '/path/to/file/b',
                    'line' => 222,
                    'function' => 'Vendor\Package\functionB',
                ),
            ),

            'Empty Trace' => array(
                array(),
                'Vendor\Package\\',
                null,
            ),
        );
    }

    /**
     * @dataProvider tracePhonyCallData
     */
    public function testTracePhonyCall($trace, $prefix, $expected)
    {
        $this->assertSame($expected, AssertionException::tracePhonyCall($trace, $prefix));
    }
}
