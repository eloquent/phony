<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2017 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Assertion\Exception;

use Eloquent\Phony as TestNamespace;
use Eloquent\Phony\ClassA;
use Eloquent\Phony\ClassB;
use PHPUnit\Framework\TestCase;

class AssertionExceptionTest extends TestCase
{
    public function testException()
    {
        $message = 'message';
        $exception = new AssertionException($message);

        $this->assertSame($message, $exception->getMessage());
        $this->assertSame(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }

    public function tracePhonyCallData()
    {
        return [
            'Method' => [
                [
                    [
                        'file' => '/path/to/file/a',
                        'line' => 111,
                        'function' => 'methodA',
                        'class' =>  ClassA::class,
                    ],
                    [
                        'file' => '/path/to/file/b',
                        'line' => 222,
                        'function' => 'methodB',
                        'class' =>  ClassB::class,
                    ],
                    [
                        'file' => '/path/to/file/c',
                        'line' => 333,
                        'function' => 'methodC',
                        'class' => 'ClassC',
                    ],
                ],
                [
                    'file' => '/path/to/file/b',
                    'line' => 222,
                    'function' => 'methodB',
                    'class' =>  ClassB::class,
                ],
            ],

            'Function' => [
                [
                    [
                        'file' => '/path/to/file/a',
                        'line' => 111,
                        'function' => 'methodA',
                        'class' =>  ClassA::class,
                    ],
                    [
                        'file' => '/path/to/file/b',
                        'line' => 222,
                        'function' => TestNamespace::class . '\functionB',
                    ],
                    [
                        'file' => '/path/to/file/c',
                        'line' => 333,
                        'function' => 'functionC',
                    ],
                ],
                [
                    'file' => '/path/to/file/b',
                    'line' => 222,
                    'function' => TestNamespace::class . '\functionB',
                ],
            ],

            'No external calls' => [
                [
                    [
                        'file' => '/path/to/file/a',
                        'line' => 111,
                        'function' => 'methodA',
                        'class' =>  ClassA::class,
                    ],
                    [
                        'file' => '/path/to/file/b',
                        'line' => 222,
                        'function' => TestNamespace::class . '\functionB',
                    ],
                ],
                [
                    'file' => '/path/to/file/b',
                    'line' => 222,
                    'function' => TestNamespace::class . '\functionB',
                ],
            ],

            'Direct construction from outside namespace' => [
                [
                    [
                        'file' => '/path/to/file/a',
                        'line' => 111,
                        'function' => 'functionA',
                    ],
                ],
                [],
            ],

            'Empty Trace' => [
                [],
                [],
            ],
        ];
    }

    /**
     * @dataProvider tracePhonyCallData
     */
    public function testTracePhonyCall($trace, $expected)
    {
        $this->assertSame($expected, AssertionException::tracePhonyCall($trace));
    }
}
