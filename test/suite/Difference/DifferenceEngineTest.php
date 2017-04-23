<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2017 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Difference;

use Eloquent\Phony\Reflection\FeatureDetector;
use PHPUnit_Framework_TestCase;
use ReflectionClass;

class DifferenceEngineTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->featureDetector = FeatureDetector::instance();
        $this->subject = new DifferenceEngine($this->featureDetector);
        $this->subject->setUseColor(false);
    }

    public function differenceData()
    {
        //                         from               to                      expected
        return array(
            'Equal'       => array('foo,bar,baz,qux', 'foo,bar,baz,qux',      'foo,bar,baz,qux'),
            'Insertion'   => array('foo,bar,baz,qux', 'foo,bar,doom,baz,qux', 'foo,bar,{+doom,+}baz,qux'),
            'Deletion'    => array('foo,bar,baz,qux', 'foo,bar,qux',          'foo,bar,[-baz,-]qux'),
            'Replacement' => array('foo,bar,baz,qux', 'foo,bar,doom,qux',     'foo,bar,[-baz-]{+doom+},qux'),
            'Unrelated'   => array('#0{}',            'foo#0{bar}',           '{+foo+}#0[-{}-]{+{bar}+}'),
        );
    }

    /**
     * @dataProvider differenceData
     */
    public function testDifference($from, $to, $expected)
    {
        $this->assertSame($expected, $this->subject->difference($from, $to));
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
