<?php

namespace Eloquent\Phony\Matcher;

use Eloquent\Phony\Exporter\InlineExporter;
use Eloquent\Phony\Mock\Builder\MockBuilderFactory;
use Eloquent\Phony\Phony;
use Eloquent\Phony\Reflection\FeatureDetector;
use Eloquent\Phony\Test\Properties\TestBaseClass;
use Eloquent\Phony\Test\Properties\TestDerivedClassA;
use Eloquent\Phony\Test\Properties\TestDerivedClassB;
use Eloquent\Phony\Test\Properties\TestDerivedClassWithTraitA;
use Eloquent\Phony\Test\Properties\TestDerivedClassWithTraitB;
use Exception;
use PHPUnit\Framework\TestCase;

class EqualToMatcherTest extends TestCase
{
    protected function setUp($value = '<string>')
    {
        $this->value = 'x';
        $this->exporter = InlineExporter::instance();
        $this->subject = new EqualToMatcher($this->value, true, $this->exporter);

        $this->featureDetector = FeatureDetector::instance();
    }

    public function testConstructor()
    {
        $this->assertSame($this->value, $this->subject->value());
    }

    public function problematicScalarValues()
    {
        return [
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
            [],
        ];
    }

    public function matchesEqualData()
    {
        $result = [
            'scalar'             => ['foo'],
            'array - sequence'   => [['foo', 'bar']],
            'array - assoc'      => [['foo' => 'bar', 'baz' => 'qux']],
            'array - nested'     => [['foo' => ['bar' => 'baz']]],
            'object - anonymous' => [(object) ['foo' => 'bar', 'baz' => 'qux']],
            'object - declared'  => [new TestDerivedClassA()],
        ];

        foreach ($this->problematicScalarValues() as $value) {
            $result[] = [$value, $value];
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

        $matcher = new EqualToMatcher($left, true, $this->exporter);
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

    public function testMatchesEqualWithTraits()
    {
        $left = new TestDerivedClassWithTraitA();
        $right = new TestDerivedClassWithTraitA();
        $matcher = new EqualToMatcher($left, true, $this->exporter);

        $this->assertTrue($matcher->matches($right));
    }

    public function matchesNotEqualData()
    {
        $result = [
            'scalar' => ['foo', 'XXX'],

            'array - sequence' => [
                ['foo', 'bar'],
                ['foo', 'XXX'],
            ],

            'array - sequence (different lengths)' => [
                ['foo'],
                ['foo', 'bar'],
            ],

            'array - sequence (different order)' => [
                ['foo', 'bar'],
                ['bar', 'foo'],
            ],

            'array - assoc (different key)' => [
                ['foo' => 'bar'],
                ['XXX' => 'bar'],
            ],

            'array - assoc (different value)' => [
                ['foo' => 'bar'],
                ['foo' => 'XXX'],
            ],

            'array - assoc (different lengths)' => [
                ['foo' => 'bar'],
                ['foo' => 'bar', 'baz' => 'qux'],
            ],

            'array - compared to non array (lhs)' => [
                '<string>',
                ['foo' => 'bar'],
            ],

            'array - compared to non array (rhs)' => [
                ['foo' => 'bar'],
                '<string>',
            ],

            'object - anonymous (different key)' => [
                (object) ['foo' => 'bar'],
                (object) ['XXX' => 'bar'],
            ],

            'object - anonymous (different value)' => [
                (object) ['foo' => 'bar'],
                (object) ['foo' => 'XXX'],
            ],

            'object - declared (different public property)' => [
                new TestDerivedClassA(),
                new TestDerivedClassA('XXX'),
            ],

            'object - declared (different private property)' => [
                new TestDerivedClassA(),
                new TestDerivedClassA(null, 'XXX'),
            ],

            'object - declared (different protected property)' => [
                new TestDerivedClassA(),
                new TestDerivedClassA(null, null, 'XXX'),
            ],

            'object - declared (same properties, different class)' => [
                new TestDerivedClassA(),
                new TestDerivedClassB(),
            ],

            'object - compared to non object (lhs)' => [
                '<string>',
                (object) ['foo' => 'bar'],
            ],

            'object - compared to non object (rhs)' => [
                (object) ['foo' => 'bar'],
                '<string>',
            ],
        ];

        $values = $this->problematicScalarValues();
        $count = count($values);

        for ($i = 0; $i < $count; ++$i) {
            for ($j = $i + 1; $j < $count; ++$j) {
                $result[] = [
                    $values[$i],
                    $values[$j],
                ];
            }
        }

        return $result;
    }

    /**
     * @dataProvider matchesNotEqualData
     */
    public function testMatchesNotEqual($left, $right)
    {
        $matcher = new EqualToMatcher($left, true, $this->exporter);

        $this->assertFalse($matcher->matches($right));
    }

    /**
     * @dataProvider matchesNotEqualData
     */
    public function testMatchesNotEqualInverse($left, $right)
    {
        $matcher = new EqualToMatcher($right, true, $this->exporter);

        $this->assertFalse($matcher->matches($left));
    }

    public function testMatchesNotEqualWithTraits()
    {
        $left = new TestDerivedClassWithTraitA();
        $matcher = new EqualToMatcher($left, true, $this->exporter);

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
        $left = (object) [];
        $left->before = 'foo';
        $left->cycle = $left;
        $left->after = 'bar';

        $right = (object) [];
        $right->before = 'foo';
        $right->cycle = $right;
        $right->after = 'bar';

        $matcher = new EqualToMatcher($left, true, $this->exporter);

        $this->assertTrue($matcher->matches($right));
    }

    public function testMatchesNotEqualWithDirectObjectCycles()
    {
        $left = (object) [];
        $left->before = 'foo';
        $left->cycle = $left;
        $left->after = 'bar';

        $right = (object) [];
        $right->before = 'foo';
        $right->cycle = $right;
        $right->after = 'XXX';

        $matcher = new EqualToMatcher($left, true, $this->exporter);

        $this->assertFalse($matcher->matches($right));
    }

    public function testMatchesEqualWithIndirectObjectCycles()
    {
        $leftA = (object) [];
        $leftA->before = 'foo';
        $leftA->cycle = $leftA;
        $leftA->after = 'bar';

        $leftB = (object) [];
        $leftB->cycle = $leftA;
        $leftA->cycle = $leftB;

        $rightA = (object) [];
        $rightA->before = 'foo';
        $rightA->cycle = $rightA;
        $rightA->after = 'bar';

        $rightB = (object) [];
        $rightB->cycle = $rightA;
        $rightA->cycle = $rightB;

        $matcher = new EqualToMatcher($leftA, true, $this->exporter);

        $this->assertTrue($matcher->matches($rightA));
    }

    public function testMatchesNotEqualWithIndirectObjectCycles()
    {
        $leftA = (object) [];
        $leftA->before = 'foo';
        $leftA->cycle = $leftA;
        $leftA->after = 'bar';

        $leftB = (object) [];
        $leftB->cycle = $leftA;
        $leftA->cycle = $leftB;

        $rightA = (object) [];
        $rightA->before = 'foo';
        $rightA->cycle = $rightA;
        $rightA->after = 'XXX';

        $rightB = (object) [];
        $rightB->cycle = $rightA;
        $rightA->cycle = $rightB;

        $matcher = new EqualToMatcher($leftA, true, $this->exporter);

        $this->assertFalse($matcher->matches($rightA));
    }

    public function testMatchesEqualWithDirectArrayCycles()
    {
        $left = [];
        $left['before'] = 'foo';
        $left['cycle'] = &$left;
        $left['after'] = 'bar';

        $right = [];
        $right['before'] = 'foo';
        $right['cycle'] = &$right;
        $right['after'] = 'bar';

        $matcher = new EqualToMatcher($left, true, $this->exporter);

        $this->assertTrue($matcher->matches($right));
    }

    public function testMatchesNotEqualWithDirectArrayCycles()
    {
        $left = [];
        $left['before'] = 'foo';
        $left['cycle'] = &$left;
        $left['after'] = 'bar';

        $right = [];
        $right['before'] = 'foo';
        $right['cycle'] = &$right;
        $right['after'] = 'XXX';

        $matcher = new EqualToMatcher($left, true, $this->exporter);

        $this->assertFalse($matcher->matches($right));
    }

    public function testMatchesEqualWithIndirectArrayCycles()
    {
        $leftA = [];
        $leftA['before'] = 'foo';
        $leftA['cycle'] = &$leftA;
        $leftA['after'] = 'bar';

        $leftB = [];
        $leftB['cycle'] = &$leftA;
        $leftA['cycle'] = &$leftB;

        $rightA = [];
        $rightA['before'] = 'foo';
        $rightA['cycle'] = &$rightA;
        $rightA['after'] = 'bar';

        $rightB = [];
        $rightB['cycle'] = &$rightA;
        $rightA['cycle'] = &$rightB;

        $matcher = new EqualToMatcher($leftA, true, $this->exporter);

        $this->assertTrue($matcher->matches($rightA));
    }

    public function testMatchesNotEqualWithIndirectArrayCycles()
    {
        $leftA = [];
        $leftA['before'] = 'foo';
        $leftA['cycle'] = &$leftA;
        $leftA['after'] = 'bar';

        $leftB = [];
        $leftB['cycle'] = &$leftA;
        $leftA['cycle'] = &$leftB;

        $rightA = [];
        $rightA['before'] = 'foo';
        $rightA['cycle'] = &$rightA;
        $rightA['after'] = 'XXX';

        $rightB = [];
        $rightB['cycle'] = &$rightA;
        $rightA['cycle'] = &$rightB;

        $matcher = new EqualToMatcher($leftA, true, $this->exporter);

        $this->assertFalse($matcher->matches($rightA));
    }

    public function testMatchesEqualWithArrayAndObjectCycle()
    {
        $leftArray = [];
        $rightArray = [];

        $left  = (object) [];
        $right = (object) [];

        $left->array = &$leftArray;
        $right->array = &$rightArray;

        $leftArray['object'] = $left;
        $rightArray['object'] = $right;

        $matcher = new EqualToMatcher($left, true, $this->exporter);

        $this->assertTrue($matcher->matches($right));
    }

    public function testMatchesArraysContainingReferencesToSameArray()
    {
        $shared = ['foo', 'bar'];
        $left  = [&$shared];
        $right = [&$shared];

        $matcher = new EqualToMatcher($left, true, $this->exporter);

        $this->assertTrue($matcher->matches($right));
    }

    public function testMockMatching()
    {
        $className = 'PhonyMockEqualToMatcherMatchesMocks';
        $builder = MockBuilderFactory::instance()->create(TestBaseClass::class)
            ->named($className);
        $mockA1 = $builder->full();
        Phony::on($mockA1)->setLabel('a');
        $mockA2 = $builder->full();
        Phony::on($mockA2)->setLabel('a');
        $mockB1 = $builder->full();
        Phony::on($mockB1)->setLabel('b');
        $mockX1 = new $className();

        $matcher = new EqualToMatcher($mockA1, true, $this->exporter);

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
        $matcher = new EqualToMatcher($left, true, $this->exporter);

        $this->assertTrue($matcher->matches($right));
    }

    public function testMatchesResetsArrayPointers()
    {
        $left = ['foo', 'bar', 'spam'];
        $right = ['foo', 'bar', 'spam'];

        next($left);

        $matcher = new EqualToMatcher($left, true, $this->exporter);

        $this->assertTrue($matcher->matches($right));
    }

    public function testMatchesIterableSpySubstitution()
    {
        $stub = Phony::stub()->setUseIterableSpies(true)->returnsArgument();
        $iterableSpyA = $stub(['a', 'b']);
        $iterableSpyB = $stub(['a', 'b']);
        $iterableSpyC = $stub(['b', 'c']);

        $matcher = new EqualToMatcher($iterableSpyA, true, $this->exporter);

        $this->assertTrue($matcher->matches(['a', 'b']));
        $this->assertTrue($matcher->matches($iterableSpyA));
        $this->assertTrue($matcher->matches($iterableSpyB));
        $this->assertFalse($matcher->matches($iterableSpyC));

        $matcher = new EqualToMatcher($iterableSpyA, false, $this->exporter);

        $this->assertFalse($matcher->matches(['a', 'b']));
        $this->assertTrue($matcher->matches($iterableSpyA));
        $this->assertFalse($matcher->matches($iterableSpyB));
        $this->assertFalse($matcher->matches($iterableSpyC));

        $matcher = new EqualToMatcher(['a', 'b'], true, $this->exporter);

        $this->assertTrue($matcher->matches(['a', 'b']));
        $this->assertTrue($matcher->matches($iterableSpyA));
        $this->assertTrue($matcher->matches($iterableSpyB));
        $this->assertFalse($matcher->matches($iterableSpyC));

        $matcher = new EqualToMatcher(['a', 'b'], false, $this->exporter);

        $this->assertTrue($matcher->matches(['a', 'b']));
        $this->assertFalse($matcher->matches($iterableSpyA));
        $this->assertFalse($matcher->matches($iterableSpyB));
        $this->assertFalse($matcher->matches($iterableSpyC));
    }

    public function testMatchesGeneratorSpySubstitution()
    {
        $functionA = eval('return function() { yield "a"; yield "b"; };');
        $functionB = eval('return function() { yield "b"; yield "c"; };');

        $generatorA = $functionA();
        $generatorB = $functionA();
        $generatorC = $functionB();

        $stub = Phony::stub()->returns($generatorA, $generatorB, $generatorC);
        $generatorSpyA = $stub();
        $generatorSpyB = $stub();
        $generatorSpyC = $stub();

        $matcher = new EqualToMatcher($generatorSpyA, true, $this->exporter);

        $this->assertTrue($matcher->matches($generatorA));
        $this->assertTrue($matcher->matches($generatorSpyA));
        $this->assertTrue($matcher->matches($generatorSpyB));
        $this->assertTrue($matcher->matches($generatorSpyC));

        $matcher = new EqualToMatcher($generatorSpyA, false, $this->exporter);

        $this->assertFalse($matcher->matches($generatorA));
        $this->assertTrue($matcher->matches($generatorSpyA));
        $this->assertTrue($matcher->matches($generatorSpyB));
        $this->assertTrue($matcher->matches($generatorSpyC));

        $matcher = new EqualToMatcher($generatorA, true, $this->exporter);

        $this->assertTrue($matcher->matches($generatorA));
        $this->assertTrue($matcher->matches($generatorSpyA));
        $this->assertTrue($matcher->matches($generatorSpyB));
        $this->assertTrue($matcher->matches($generatorSpyC));

        $matcher = new EqualToMatcher($generatorA, false, $this->exporter);

        $this->assertTrue($matcher->matches($generatorA));
        $this->assertFalse($matcher->matches($generatorSpyA));
        $this->assertFalse($matcher->matches($generatorSpyB));
        $this->assertFalse($matcher->matches($generatorSpyC));
    }

    public function testMatchesInstanceHandleSubstitution()
    {
        $handle = Phony::mock();
        $mock = $handle->get();

        $matcher = new EqualToMatcher($handle, true, $this->exporter);

        $this->assertTrue($matcher->matches($handle));
        $this->assertTrue($matcher->matches($mock));

        $matcher = new EqualToMatcher($handle, false, $this->exporter);

        $this->assertTrue($matcher->matches($handle));
        $this->assertFalse($matcher->matches($mock));

        $matcher = new EqualToMatcher($mock, true, $this->exporter);

        $this->assertTrue($matcher->matches($handle));
        $this->assertTrue($matcher->matches($mock));

        $matcher = new EqualToMatcher($mock, false, $this->exporter);

        $this->assertFalse($matcher->matches($handle));
        $this->assertTrue($matcher->matches($mock));
    }

    public function testDescribe()
    {
        $this->assertSame('"x"', $this->subject->describe());
        $this->assertSame('"x"', $this->subject->describe($this->exporter));
        $this->assertSame('"x"', strval($this->subject));
    }

    public function testDescribeWithMultilineString()
    {
        $this->subject = new EqualToMatcher("line\nline", true, $this->exporter);

        $this->assertSame('"line\nline"', $this->subject->describe());
    }

    public function testDescribeWithNonString()
    {
        $this->subject = new EqualToMatcher(111, true, $this->exporter);

        $this->assertSame('111', $this->subject->describe());
    }
}
