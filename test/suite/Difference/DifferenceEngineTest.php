<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Difference;

use PHPUnit_Framework_TestCase;
use ReflectionClass;

class DifferenceEngineTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->subject = new DifferenceEngine();
    }

    public function differenceData()
    {
        return array(
            'Addition at start' => array(
                array(
                    'c',
                    'd',
                ),
                array(
                    'a',
                    'b',
                    'c',
                    'd',
                ),
                array(
                    array('+', 'a'),
                    array('+', 'b'),
                    array(' ', 'c'),
                    array(' ', 'd'),
                ),
            ),

            'Addition in middle' => array(
                array(
                    'a',
                    'd',
                ),
                array(
                    'a',
                    'b',
                    'c',
                    'd',
                ),
                array(
                    array(' ', 'a'),
                    array('+', 'b'),
                    array('+', 'c'),
                    array(' ', 'd'),
                ),
            ),

            'Addition at end' => array(
                array(
                    'a',
                    'b',
                ),
                array(
                    'a',
                    'b',
                    'c',
                    'd',
                ),
                array(
                    array(' ', 'a'),
                    array(' ', 'b'),
                    array('+', 'c'),
                    array('+', 'd'),
                ),
            ),

            'Removal at start' => array(
                array(
                    'a',
                    'b',
                    'c',
                    'd',
                ),
                array(
                    'c',
                    'd',
                ),
                array(
                    array('-', 'a'),
                    array('-', 'b'),
                    array(' ', 'c'),
                    array(' ', 'd'),
                ),
            ),

            'Removal in middle' => array(
                array(
                    'a',
                    'b',
                    'c',
                    'd',
                ),
                array(
                    'a',
                    'd',
                ),
                array(
                    array(' ', 'a'),
                    array('-', 'b'),
                    array('-', 'c'),
                    array(' ', 'd'),
                ),
            ),

            'Removal at end' => array(
                array(
                    'a',
                    'b',
                    'c',
                    'd',
                ),
                array(
                    'a',
                    'b',
                ),
                array(
                    array(' ', 'a'),
                    array(' ', 'b'),
                    array('-', 'c'),
                    array('-', 'd'),
                ),
            ),

            'Banana to atana' => array(
                array(
                    'b',
                    'a',
                    'n',
                    'a',
                    'n',
                    'a',
                ),
                array(
                    'a',
                    't',
                    'a',
                    'n',
                    'a',
                ),
                array(
                    array('-', 'b'),
                    array(' ', 'a'),
                    array('-', 'n'),
                    array('+', 't'),
                    array(' ', 'a'),
                    array(' ', 'n'),
                    array(' ', 'a'),
                ),
            ),

            'Lao to tzu' => array(
                array(
                    'The Way that can be told of is not the eternal Way;',
                    'The name that can be named is not the eternal name.',
                    'The Nameless is the origin of Heaven and Earth;',
                    'The Named is the mother of all things.',
                    'Therefore let there always be non-being,',
                    '  so we may see their subtlety,',
                    'And let there always be being,',
                    '  so we may see their outcome.',
                    'The two are the same,',
                    'But after they are produced,',
                    '  they have different names.',
                ),
                array(
                    'The Nameless is the origin of Heaven and Earth;',
                    'The named is the mother of all things.',
                    '',
                    'Therefore let there always be non-being,',
                    '  so we may see their subtlety,',
                    'And let there always be being,',
                    '  so we may see their outcome.',
                    'The two are the same,',
                    'But after they are produced,',
                    '  they have different names.',
                    'They both may be called deep and profound.',
                    'Deeper and more profound,',
                    'The door of all subtleties!',
                ),
                array(
                    array('-', 'The Way that can be told of is not the eternal Way;'),
                    array('-', 'The name that can be named is not the eternal name.'),
                    array(' ', 'The Nameless is the origin of Heaven and Earth;'),
                    array('-', 'The Named is the mother of all things.'),
                    array('+', 'The named is the mother of all things.'),
                    array('+', ''),
                    array(' ', 'Therefore let there always be non-being,'),
                    array(' ', '  so we may see their subtlety,'),
                    array(' ', 'And let there always be being,'),
                    array(' ', '  so we may see their outcome.'),
                    array(' ', 'The two are the same,'),
                    array(' ', 'But after they are produced,'),
                    array(' ', '  they have different names.'),
                    array('+', 'They both may be called deep and profound.'),
                    array('+', 'Deeper and more profound,'),
                    array('+', 'The door of all subtleties!'),
                ),
            ),
        );
    }

    /**
     * @dataProvider differenceData
     */
    public function testDifference($from, $to, $expected)
    {
        $this->assertEquals($expected, $this->subject->difference($from, $to));
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
