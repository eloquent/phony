<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2015 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Matcher;

use Eloquent\Phony\Exporter\InlineExporter;
use Eloquent\Phony\Mock\Builder\Factory\MockBuilderFactory;
use Eloquent\Phony\Mock\Factory\MockFactory;
use Eloquent\Phony\Test\Properties\TestDerivedClassA;
use Eloquent\Phony\Test\Properties\TestDerivedClassB;
use Eloquent\Phony\Test\Properties\TestDerivedClassWithTraitA;
use Eloquent\Phony\Test\Properties\TestDerivedClassWithTraitB;
use Exception;
use PHPUnit_Framework_TestCase;

class EqualToMatcherTest extends PHPUnit_Framework_TestCase
{
    protected function setUp($value = '<string>')
    {
        $this->value = 'x';
        $this->exporter = new InlineExporter(false);
        $this->subject = new EqualToMatcher($this->value, $this->exporter);
    }

    public function testConstructor()
    {
        $this->assertSame($this->value, $this->subject->value());
        $this->assertSame($this->exporter, $this->subject->exporter());
    }

    public function testConstructorDefaults()
    {
        $this->subject = new EqualToMatcher($this->value);

        $this->assertSame(InlineExporter::instance(), $this->subject->exporter());
    }

    public function problematicScalarValues()
    {
        return array(
            true,
            false,
            null,
            '',
            '0',
            '1',
            '-1',
            0,
            1,
            -1,
            0.0,
            1.0,
            -1.0,
            array(),
        );
    }

    public function matchesEqualData()
    {
        $result = array(
            'scalar'             => array('foo'),
            'array - sequence'   => array(array('foo', 'bar')),
            'array - assoc'      => array(array('foo' => 'bar', 'baz' => 'qux')),
            'array - nested'     => array(array('foo' => array('bar' => 'baz'))),
            'object - anonymous' => array((object) array('foo' => 'bar', 'baz' => 'qux')),
            'object - declared'  => array(new TestDerivedClassA()),
        );

        foreach ($this->problematicScalarValues() as $value) {
            $result[] = array($value, $value);
        }

        return $result;
    }

    /**
     * @dataProvider matchesEqualData
     */
    public function testMatchesEqual($left)
    {
        // deep clone
        $snapshot = unserialize(serialize($left));
        $right = unserialize(serialize($left));

        $matcher = new EqualToMatcher($left);
        $this->assertTrue($matcher->matches($right));

        // ensure that the comparison does not modify the arguments in any way
        $this->assertEquals(
            $snapshot,
            $left
        );

        $this->assertEquals(
            $snapshot,
            $right
        );
    }

    /**
     * @requires PHP 5.4.0-dev
     */
    public function testMatchesEqualWithTraits()
    {
        $left = new TestDerivedClassWithTraitA();
        $right = new TestDerivedClassWithTraitA();
        $matcher = new EqualToMatcher($left);

        $this->assertTrue($matcher->matches($right));
    }

    public function matchesNotEqualData()
    {
        $result = array(
            'scalar' => array('foo', 'XXX'),

            'array - sequence' => array(
                array('foo', 'bar'),
                array('foo', 'XXX'),
            ),

            'array - sequence (different lengths)' => array(
                array('foo'),
                array('foo', 'bar'),
            ),

            'array - sequence (different order)' => array(
                array('foo', 'bar'),
                array('bar', 'foo'),
            ),

            'array - assoc (different key)' => array(
                array('foo' => 'bar'),
                array('XXX' => 'bar'),
            ),

            'array - assoc (different value)' => array(
                array('foo' => 'bar'),
                array('foo' => 'XXX'),
            ),

            'array - assoc (different lengths)' => array(
                array('foo' => 'bar'),
                array('foo' => 'bar', 'baz' => 'qux'),
            ),

            'array - compared to non array (lhs)' => array(
                '<string>',
                array('foo' => 'bar'),
            ),

            'array - compared to non array (rhs)' => array(
                array('foo' => 'bar'),
                '<string>',
            ),

            'object - anonymous (different key)' => array(
                (object) array('foo' => 'bar'),
                (object) array('XXX' => 'bar'),
            ),

            'object - anonymous (different value)' => array(
                (object) array('foo' => 'bar'),
                (object) array('foo' => 'XXX'),
            ),

            'object - declared (different public property)' => array(
                new TestDerivedClassA(),
                new TestDerivedClassA('XXX'),
            ),

            'object - declared (different private property)' => array(
                new TestDerivedClassA(),
                new TestDerivedClassA(null, 'XXX'),
            ),

            'object - declared (different protected property)' => array(
                new TestDerivedClassA(),
                new TestDerivedClassA(null, null, 'XXX'),
            ),

            'object - declared (same properties, different class)' => array(
                new TestDerivedClassA(),
                new TestDerivedClassB(),
            ),

            'object - compared to non object (lhs)' => array(
                '<string>',
                (object) array('foo' => 'bar'),
            ),

            'object - compared to non object (rhs)' => array(
                (object) array('foo' => 'bar'),
                '<string>',
            ),
        );

        $values = $this->problematicScalarValues();
        $count = count($values);

        for ($i = 0; $i < $count; ++$i) {
            for ($j = $i + 1; $j < $count; ++$j) {
                $result[] = array(
                    $values[$i],
                    $values[$j],
                );
            }
        }

        return $result;
    }

    /**
     * @dataProvider matchesNotEqualData
     */
    public function testMatchesNotEqual($left, $right)
    {
        $matcher = new EqualToMatcher($left);

        $this->assertFalse($matcher->matches($right));
    }

    /**
     * @dataProvider matchesNotEqualData
     */
    public function testMatchesNotEqualInverse($left, $right)
    {
        $matcher = new EqualToMatcher($right);

        $this->assertFalse($matcher->matches($left));
    }

    /**
     * @requires PHP 5.4.0-dev
     */
    public function testMatchesNotEqualWithTraits()
    {
        $left = new TestDerivedClassWithTraitA();
        $matcher = new EqualToMatcher($left);

        // different public property
        $right = new TestDerivedClassWithTraitA('XXX');

        $this->assertFalse($matcher->matches($right));

        // different private property
        $right = new TestDerivedClassWithTraitA(null, 'XXX');

        $this->assertFalse($matcher->matches($right));

        // different protected property
        $right = new TestDerivedClassWithTraitA(null, null, 'XXX');

        $this->assertFalse($matcher->matches($right));

        // same properties, different class
        $right = new TestDerivedClassWithTraitB();

        $this->assertFalse($matcher->matches($right));
    }

    public function testMatchesEqualWithDirectObjectCycles()
    {
        $left = (object) array();
        $left->before = 'foo';
        $left->cycle = $left;
        $left->after = 'bar';

        $right = (object) array();
        $right->before = 'foo';
        $right->cycle = $right;
        $right->after = 'bar';

        $matcher = new EqualToMatcher($left);

        $this->assertTrue($matcher->matches($right));
    }

    public function testMatchesNotEqualWithDirectObjectCycles()
    {
        $left = (object) array();
        $left->before = 'foo';
        $left->cycle = $left;
        $left->after = 'bar';

        $right = (object) array();
        $right->before = 'foo';
        $right->cycle = $right;
        $right->after = 'XXX';

        $matcher = new EqualToMatcher($left);

        $this->assertFalse($matcher->matches($right));
    }

    public function testMatchesEqualWithIndirectObjectCycles()
    {
        $leftA = (object) array();
        $leftA->before = 'foo';
        $leftA->cycle = $leftA;
        $leftA->after = 'bar';

        $leftB = (object) array();
        $leftB->cycle = $leftA;
        $leftA->cycle = $leftB;

        $rightA = (object) array();
        $rightA->before = 'foo';
        $rightA->cycle = $rightA;
        $rightA->after = 'bar';

        $rightB = (object) array();
        $rightB->cycle = $rightA;
        $rightA->cycle = $rightB;

        $matcher = new EqualToMatcher($leftA);

        $this->assertTrue($matcher->matches($rightA));
    }

    public function testMatchesNotEqualWithIndirectObjectCycles()
    {
        $leftA = (object) array();
        $leftA->before = 'foo';
        $leftA->cycle = $leftA;
        $leftA->after = 'bar';

        $leftB = (object) array();
        $leftB->cycle = $leftA;
        $leftA->cycle = $leftB;

        $rightA = (object) array();
        $rightA->before = 'foo';
        $rightA->cycle = $rightA;
        $rightA->after = 'XXX';

        $rightB = (object) array();
        $rightB->cycle = $rightA;
        $rightA->cycle = $rightB;

        $matcher = new EqualToMatcher($leftA);

        $this->assertFalse($matcher->matches($rightA));
    }

    public function testMatchesEqualWithDirectArrayCycles()
    {
        $left = array();
        $left['before'] = 'foo';
        $left['cycle'] = &$left;
        $left['after'] = 'bar';

        $right = array();
        $right['before'] = 'foo';
        $right['cycle'] = &$right;
        $right['after'] = 'bar';

        $matcher = new EqualToMatcher($left);

        $this->assertTrue($matcher->matches($right));
    }

    public function testMatchesNotEqualWithDirectArrayCycles()
    {
        $left = array();
        $left['before'] = 'foo';
        $left['cycle'] = &$left;
        $left['after'] = 'bar';

        $right = array();
        $right['before'] = 'foo';
        $right['cycle'] = &$right;
        $right['after'] = 'XXX';

        $matcher = new EqualToMatcher($left);

        $this->assertFalse($matcher->matches($right));
    }

    public function testMatchesEqualWithIndirectArrayCycles()
    {
        $leftA = array();
        $leftA['before'] = 'foo';
        $leftA['cycle'] = &$leftA;
        $leftA['after'] = 'bar';

        $leftB = array();
        $leftB['cycle'] = &$leftA;
        $leftA['cycle'] = &$leftB;

        $rightA = array();
        $rightA['before'] = 'foo';
        $rightA['cycle'] = &$rightA;
        $rightA['after'] = 'bar';

        $rightB = array();
        $rightB['cycle'] = &$rightA;
        $rightA['cycle'] = &$rightB;

        $matcher = new EqualToMatcher($leftA);

        $this->assertTrue($matcher->matches($rightA));
    }

    public function testMatchesNotEqualWithIndirectArrayCycles()
    {
        $leftA = array();
        $leftA['before'] = 'foo';
        $leftA['cycle'] = &$leftA;
        $leftA['after'] = 'bar';

        $leftB = array();
        $leftB['cycle'] = &$leftA;
        $leftA['cycle'] = &$leftB;

        $rightA = array();
        $rightA['before'] = 'foo';
        $rightA['cycle'] = &$rightA;
        $rightA['after'] = 'XXX';

        $rightB = array();
        $rightB['cycle'] = &$rightA;
        $rightA['cycle'] = &$rightB;

        $matcher = new EqualToMatcher($leftA);

        $this->assertFalse($matcher->matches($rightA));
    }

    public function testMatchesEqualWithArrayAndObjectCycle()
    {
        $leftArray = array();
        $rightArray = array();

        $left  = (object) array();
        $right = (object) array();

        $left->array = &$leftArray;
        $right->array = &$rightArray;

        $leftArray['object'] = $left;
        $rightArray['object'] = $right;

        $matcher = new EqualToMatcher($left);

        $this->assertTrue($matcher->matches($right));
    }

    public function testMatchesArraysContainingReferencesToSameArray()
    {
        $shared = array('foo', 'bar');
        $left  = array(&$shared);
        $right = array(&$shared);

        $matcher = new EqualToMatcher($left);

        $this->assertTrue($matcher->matches($right));
    }

    public function testMockMatching()
    {
        $mockBuilderFactory = MockBuilderFactory::instance();
        $mockFactory = MockFactory::instance();
        $className = 'PhonyMockEqualToMatcherMatchesMocks';
        $builder = $mockBuilderFactory->create('Eloquent\Phony\Test\Properties\TestBaseClass', null, $className);
        $mockA1 = $mockFactory->createMock($builder, null, 'a');
        $mockA2 = $mockFactory->createMock($builder, null, 'a');
        $mockB1 = $mockFactory->createMock($builder, null, 'b');
        $mockX1 = new $className();

        $matcher = new EqualToMatcher($mockA1);

        $this->assertTrue($matcher->matches($mockA2));
        $this->assertFalse($matcher->matches($mockB1));
        $this->assertFalse($matcher->matches($mockX1));

        $mockA2->basePublic = 'x';

        $this->assertFalse($matcher->matches($mockA2));
    }

    public function testMatchesExceptions()
    {
        $left  = new Exception('The message.', 123);
        $right = new Exception('The message.', 123);
        $matcher = new EqualToMatcher($left);

        $this->assertTrue($matcher->matches($right));
    }

    public function testMatchesResetsArrayPointers()
    {
        $left = array('foo', 'bar', 'spam');
        $right = array('foo', 'bar', 'spam');

        next($left);

        $matcher = new EqualToMatcher($left);

        $this->assertTrue($matcher->matches($right));
    }

    public function testDescribe()
    {
        $this->assertSame('"x"', $this->subject->describe());
    }

    public function testDescribeWithMultilineString()
    {
        $this->subject = new EqualToMatcher("line\nline");

        $this->assertSame('"line\nline"', $this->subject->describe());
    }

    public function testDescribeWithNonString()
    {
        $this->subject = new EqualToMatcher(111);

        $this->assertSame('111', $this->subject->describe());
    }
}
