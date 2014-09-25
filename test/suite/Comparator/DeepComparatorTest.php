<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Comparator;

use DateTime;
use Eloquent\Phony\Test\ChildClass;
use Eloquent\Phony\Test\ParentClass;
use Eloquent\Phony\Test\TestComparator;
use PHPUnit_Framework_TestCase;
use ReflectionClass;
use stdClass;

class DeepComparatorTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $comparator = new StrictPhpComparator();
        $this->fallbackComparator = new TestComparator(
            function ($left, $right) use ($comparator) {
                return $comparator->compare($left, $right);
            }
        );
        $this->subject = new DeepComparator($this->fallbackComparator);
    }

    public function testConstructor()
    {
        $this->subject = new DeepComparator($this->fallbackComparator, true);

        $this->assertSame($this->fallbackComparator, $this->subject->fallbackComparator());
        $this->assertTrue($this->subject->relaxClassComparisons());
    }

    public function testConstructorDefaults()
    {
        $this->subject = new DeepComparator();

        $this->assertSame(StrictPhpComparator::instance(), $this->subject->fallbackComparator());
        $this->assertFalse($this->subject->relaxClassComparisons());
    }

    public function testInvoke()
    {
        $this->assertSame(0, call_user_func($this->subject, array(1, 2, 3), array(1, 2, 3)));
    }

    public function testCompareWithObjectReferences()
    {
        $value = (object) array('foo' => 'bar');

        $this->assertSame(0, $this->subject->compare($value, $value));
        $this->assertSame(array(), $this->fallbackComparator->calls());
    }

    public function testCompareWithEmptyArrays()
    {
        $this->assertSame(0, $this->subject->compare(array(), array()));
        $this->assertSame(array(), $this->fallbackComparator->calls());
    }

    public function testCompareWithArrays()
    {
        $this->assertSame(0, $this->subject->compare(array(1, 2, 3), array(1, 2, 3)));
    }

    public function testCompareWithAssociativeArrays()
    {
        $this->assertSame(
            0,
            $this->subject->compare(array('a' => 1, 'b' => 2, 'c' => 3), array('a' => 1, 'b' => 2, 'c' => 3))
        );
    }

    public function testCompareWithArraysThatDifferBySize()
    {
        $this->assertLessThan(0, $this->subject->compare(array(1, 2), array(1, 2, 3)));
        $this->assertGreaterThan(0, $this->subject->compare(array(1, 2, 3), array(1, 2)));
    }

    public function testCompareWithArraysThatDifferBySizeAndContent()
    {
        $this->assertLessThan(0, $this->subject->compare(array(1, 2, 3), array(1, 3)));
        $this->assertGreaterThan(0, $this->subject->compare(array(1, 3), array(1, 2, 3)));
    }

    public function testCompareWithArraysThatDifferByKeys()
    {
        $this->assertLessThan(0, $this->subject->compare(array('a' => 1), array('b' => 1)));
        $this->assertGreaterThan(0, $this->subject->compare(array('b' => 1), array('a' => 1)));
    }

    public function testCompareWithObjects()
    {
        $this->assertSame(
            0,
            $this->subject->compare(
                (object) array('a' => 1, 'b' => 2, 'c' => 3),
                (object) array('a' => 1, 'b' => 2, 'c' => 3)
            )
        );
    }

    public function testCompareWithObjectsThatDifferBySize()
    {
        $this->assertLessThan(
            0,
            $this->subject->compare((object) array('a' => 1, 'b' => 2), (object) array('a' => 1, 'b' => 2, 'c' => 3))
        );

        $this->assertGreaterThan(
            0,
            $this->subject->compare((object) array('a' => 1, 'b' => 2, 'c' => 3), (object) array('a' => 1, 'b' => 2))
        );
    }

    public function testCompareWithObjectsThatDifferBySizeAndContent()
    {
        $this->assertLessThan(
            0,
            $this->subject->compare((object) array('a' => 1, 'b' => 2, 'c' => 3), (object) array('a' => 1, 'b' => 3))
        );

        $this->assertGreaterThan(
            0,
            $this->subject->compare((object) array('a' => 1, 'b' => 3), (object) array('a' => 1, 'b' => 2, 'c' => 3))
        );
    }

    public function testCompareWithObjectsThatDifferClassName()
    {
        $this->assertLessThan(0, $this->subject->compare(new DateTime(), new stdClass()));
        $this->assertGreaterThan(0, $this->subject->compare(new stdClass(), new DateTime()));
    }

    public function testCompareWithObjectsWithRelaxedClassComparisons()
    {
        $this->subject = new DeepComparator($this->fallbackComparator, true);

        $this->assertSame(0, $this->subject->compare(new ParentClass(1, 2), new ChildClass(1, 2)));
    }

    public function testCompareWithObjectsDifferentInnerClassTypes()
    {
        $obj1 = new stdClass();
        $obj1->foo = new stdClass();
        $obj2 = new stdClass();
        $obj2->foo = new ParentClass(0, 0);

        $this->assertSame(0, $this->subject->compare($obj1, $obj1));
        $this->assertSame(0, $this->subject->compare($obj2, $obj2));
        $this->assertLessThan(0, $this->subject->compare($obj2, $obj1));
        $this->assertGreaterThan(0, $this->subject->compare($obj1, $obj2));
    }

    public function testCompareWithObjectsParentAndDerived()
    {
        $obj1 = new ParentClass(0, 0);
        $obj2 = new ChildClass(0, 0);

        $this->assertSame(0, $this->subject->compare($obj1, $obj1));
        $this->assertSame(0, $this->subject->compare($obj2, $obj2));
        $this->assertLessThan(0, $this->subject->compare($obj2, $obj1));
        $this->assertGreaterThan(0, $this->subject->compare($obj1, $obj2));
    }

    public function testCompareWithObjectsHavingSharedInnerObject()
    {
        $shared = new ParentClass('foo', 'bar');
        $obj1 = new ParentClass(111, $shared);
        $obj2 = new ParentClass(222, $shared);
        $obj3 = new ChildClass(333, $shared);
        $obj4 = new ChildClass(444, $shared);

        $this->assertSame(0, $this->subject->compare($obj1, $obj1));
        $this->assertSame(0, $this->subject->compare($obj3, $obj3));
        $this->assertLessThan(0, $this->subject->compare($obj1, $obj2));
        $this->assertGreaterThan(0, $this->subject->compare($obj2, $obj1));
        $this->assertLessThan(0, $this->subject->compare($obj3, $obj4));
        $this->assertGreaterThan(0, $this->subject->compare($obj4, $obj3));
    }

    public function testCompareWithSimpleRecursion()
    {
        $obj1 = new stdClass();
        $obj1->foo = $obj1;
        $obj1->bar = 1;
        $obj2 = new stdClass();
        $obj2->foo = $obj2;
        $obj2->bar = 2;

        // The first property compared is infinitely recusive, so just the hash will be used.
        // Since the hash's wont match the 'bar' property will not be compared.
        if (spl_object_hash($obj1) < spl_object_hash($obj2)) {
            $this->assertLessThan(0, $this->subject->compare($obj1, $obj2));
            $this->assertGreaterThan(0, $this->subject->compare($obj2, $obj1));
        } else {
            $this->assertLessThan(0, $this->subject->compare($obj2, $obj1));
            $this->assertGreaterThan(0, $this->subject->compare($obj1, $obj2));
        }
    }

    public function testCompareWithSimpleObjectsDoubleRecursion()
    {
        $obj1 = new stdClass();
        $obj1->recurse = new stdClass();
        $obj1->recurse->recurse = $obj1;
        $obj1->value = 1;
        $obj2 = new stdClass();
        $obj2->recurse = new stdClass();
        $obj2->recurse->recurse = $obj2;
        $obj2->value = 2;

        // The first property compared is infinitely recusive, so just the hash will be used.
        // Since the hash's wont match the 'value' property will not be compared.
        if (spl_object_hash($obj1) < spl_object_hash($obj2)) {
            $this->assertLessThan(0, $this->subject->compare($obj1, $obj2));
            $this->assertGreaterThan(0, $this->subject->compare($obj2, $obj1));
        } else {
            $this->assertLessThan(0, $this->subject->compare($obj2, $obj1));
            $this->assertGreaterThan(0, $this->subject->compare($obj1, $obj2));
        }
    }

    public function testCompareWithSimpleObjectsBothHavingObject1AsFirstProperty()
    {
        $obj1 = new stdClass();
        $obj1->foo = $obj1;
        $obj1->bar = 1;
        $obj2 = new stdClass();
        $obj2->foo = $obj1;
        $obj2->bar = 2;

        $this->assertLessThan(0, $this->subject->compare($obj1, $obj2));
        $this->assertGreaterThan(0, $this->subject->compare($obj2, $obj1));
    }

    public function testCompareWithObjectCycle()
    {
        $obj1 = new stdClass();
        $obj1->foo = new ParentClass('foo1', $obj1);
        $obj2 = new stdClass();
        $obj2->foo = new ParentClass('foo2', $obj2);
        $obj3 = new stdClass();
        $obj3->foo = new ChildClass('bar3', $obj1);
        $obj4 = new stdClass();
        $obj4->foo = new ChildClass('bar4', $obj2);

        $this->assertLessThan(0, $this->subject->compare($obj1, $obj2));
        $this->assertGreaterThan(0, $this->subject->compare($obj2, $obj1));
        $this->assertLessThan(0, $this->subject->compare($obj3, $obj4));
        $this->assertGreaterThan(0, $this->subject->compare($obj4, $obj3));
    }

    public function testCompareWithObjectsHavingInternalArraysAndObjects()
    {
        $shared = new ChildClass('foo', 'bar');
        $obj1 = new ParentClass(array('a', 'b'), array($shared, 'foo'));
        $obj2 = new ParentClass(array('a', 'b'), array($shared, 'foo'));
        $obj3 = new ParentClass(array('x', 'y'), array($shared, 'foo'));

        $this->assertSame(0, $this->subject->compare($obj1, $obj1));
        $this->assertSame(0, $this->subject->compare($obj1, $obj2));
        $this->assertSame(0, $this->subject->compare($obj3, $obj3));
        $this->assertLessThan(0, $this->subject->compare($obj1, $obj3));
        $this->assertGreaterThan(0, $this->subject->compare($obj3, $obj1));
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
