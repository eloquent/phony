<?php

declare(strict_types=1);

namespace Eloquent\Phony\Matcher;

use AllowDynamicProperties;
use Eloquent\Phony\Test\Facade\FacadeContainer;
use PHPUnit\Framework\TestCase;

#[AllowDynamicProperties]
class MatcherVerifierTest extends TestCase
{
    protected function setUp(): void
    {
        $container = new FacadeContainer();
        $this->subject = $container->matcherVerifier;

        $this->matcherFactory = $container->matcherFactory;
        $this->arguments = ['a', 'b', 'c'];
    }

    public function matchesData()
    {
        //                               matchers              isValid
        return [
            'Exact arguments'        => [['a', 'b', 'c'],      true],
            'Empty'                  => [[],                   false],
            'First arguments'        => [['a', 'b'],           false],
            'Single argument'        => [['a'],                false],
            'Last arguments'         => [['b', 'c'],           false],
            'Last argument'          => [['c'],                false],
            'Extra arguments'        => [['a', 'b', 'c', 'd'], false],
            'First argument differs' => [['d', 'b', 'c'],      false],
            'Last argument differs'  => [['a', 'b', 'd'],      false],
            'Unused argument'        => [['d'],                false],
        ];
    }

    /**
     * @dataProvider matchesData
     */
    public function testMatches(array $arguments, $isValid)
    {
        $matchers = $this->matcherFactory->adaptAll($arguments);
        $explain = $this->subject->explain($matchers, $this->arguments);

        $this->assertSame($isValid, $this->subject->matches($matchers, $this->arguments));
        $this->assertSame($isValid, $explain->isMatch);
    }

    public function testMatchesExplicitArgumentExistence()
    {
        $matchers = [$this->matcherFactory->equalTo(null)];

        $this->assertTrue($this->subject->matches($matchers, [null]));
        $this->assertFalse($this->subject->matches($matchers, []));
    }

    public function testMatchesWithWildcardAfterValue()
    {
        $matchers = [
            $this->matcherFactory->equalTo('a'),
            new WildcardMatcher($this->matcherFactory->equalTo('b'), 0, -1),
        ];

        $this->assertTrue($this->subject->matches($matchers, ['a']));
        $this->assertTrue($this->subject->matches($matchers, ['a', 'b']));
        $this->assertTrue($this->subject->matches($matchers, ['a', 'b', 'b']));
        $this->assertFalse($this->subject->matches($matchers, ['a', 'x']));
        $this->assertFalse($this->subject->matches($matchers, ['a', 'b', 'x']));
        $this->assertFalse($this->subject->matches($matchers, ['a', 'x', 'b']));
        $this->assertFalse($this->subject->matches($matchers, ['a', 'x', 'x']));
    }

    public function testMatchesWithWildcardBeforeValue()
    {
        $matchers = [
            new WildcardMatcher($this->matcherFactory->equalTo('b'), 0, -1),
            $this->matcherFactory->equalTo('a'),
        ];

        $this->assertTrue($this->subject->matches($matchers, ['a']));
        $this->assertTrue($this->subject->matches($matchers, ['b', 'a']));
        $this->assertTrue($this->subject->matches($matchers, ['b', 'b', 'a']));
        $this->assertFalse($this->subject->matches($matchers, ['x', 'a']));
        $this->assertFalse($this->subject->matches($matchers, ['b', 'x', 'a']));
        $this->assertFalse($this->subject->matches($matchers, ['x', 'b', 'a']));
        $this->assertFalse($this->subject->matches($matchers, ['x', 'x', 'a']));
    }

    public function testMatchesWithWildcardBeforeValueGreedy()
    {
        $matchers = [
            new WildcardMatcher($this->matcherFactory->equalTo('a'), 0, -1),
            $this->matcherFactory->equalTo('a'),
        ];

        $this->assertFalse($this->subject->matches($matchers, ['a', 'a']));
    }

    public function testMatchesWithOnlyWildcard()
    {
        $matchers = [new WildcardMatcher($this->matcherFactory->equalTo('b'), 0, -1)];

        $this->assertTrue($this->subject->matches($matchers, []));
        $this->assertTrue($this->subject->matches($matchers, ['b']));
        $this->assertTrue($this->subject->matches($matchers, ['b', 'b']));
        $this->assertFalse($this->subject->matches($matchers, ['x']));
        $this->assertFalse($this->subject->matches($matchers, ['b', 'x']));
        $this->assertFalse($this->subject->matches($matchers, ['x', 'b']));
        $this->assertFalse($this->subject->matches($matchers, ['x', 'x']));
    }

    public function testMatchesWithWildcardMinimumArguments()
    {
        $matchers = [
            $this->matcherFactory->equalTo('a'),
            new WildcardMatcher($this->matcherFactory->equalTo('b'), 1, -1),
        ];

        $this->assertFalse($this->subject->matches($matchers, ['a']));
        $this->assertTrue($this->subject->matches($matchers, ['a', 'b']));
        $this->assertTrue($this->subject->matches($matchers, ['a', 'b', 'b']));
        $this->assertFalse($this->subject->matches($matchers, ['a', 'x']));
        $this->assertFalse($this->subject->matches($matchers, ['a', 'b', 'x']));
        $this->assertFalse($this->subject->matches($matchers, ['a', 'x', 'b']));
        $this->assertFalse($this->subject->matches($matchers, ['a', 'x', 'x']));
    }

    public function testMatchesWithWildcardMaximumArguments()
    {
        $matchers = [
            $this->matcherFactory->equalTo('a'),
            new WildcardMatcher($this->matcherFactory->equalTo('b'), 0, 1),
        ];

        $this->assertTrue($this->subject->matches($matchers, ['a']));
        $this->assertTrue($this->subject->matches($matchers, ['a', 'b']));
        $this->assertFalse($this->subject->matches($matchers, ['a', 'b', 'b']));
        $this->assertFalse($this->subject->matches($matchers, ['a', 'x']));
        $this->assertFalse($this->subject->matches($matchers, ['a', 'b', 'x']));
        $this->assertFalse($this->subject->matches($matchers, ['a', 'x', 'b']));
        $this->assertFalse($this->subject->matches($matchers, ['a', 'x', 'x']));
    }

    public function explainData()
    {
        //                               matchers              isMatch matcherMatches             argumentMatches
        return [
            'Exact arguments'        => [['a', 'b', 'c'],      true,   [true, true, true],        [true, true, true]],
            'Empty'                  => [[],                   false,  [],                        [false, false, false]],
            'First arguments'        => [['a', 'b'],           false,  [true, true],              [true, true, false]],
            'Single argument'        => [['a'],                false,  [true],                    [true, false, false]],
            'Last arguments'         => [['b', 'c'],           false,  [false, false],            [false, false, false]],
            'Last argument'          => [['c'],                false,  [false],                   [false, false, false]],
            'Extra arguments'        => [['a', 'b', 'c', 'd'], false,  [true, true, true, false], [true, true, true, false]],
            'First argument differs' => [['d', 'b', 'c'],      false,  [false, true, true],       [false, true, true]],
            'Last argument differs'  => [['a', 'b', 'd'],      false,  [true, true, false],       [true, true, false]],
            'Unused argument'        => [['d'],                false,  [false],                   [false, false, false]],
        ];
    }

    /**
     * @dataProvider explainData
     */
    public function testExplain(array $arguments, $isMatch, $matcherMatches, $argumentMatches)
    {
        $matchers = $this->matcherFactory->adaptAll($arguments);

        $this->assertEquals(
            new MatcherResult($isMatch, $matcherMatches, $argumentMatches),
            $this->subject->explain($matchers, $this->arguments)
        );
        $this->assertSame($isMatch, $this->subject->matches($matchers, $this->arguments));
    }

    public function testExplainExplicitArgumentExistence()
    {
        $matchers = [$this->matcherFactory->equalTo(null)];

        $this->assertEquals(
            new MatcherResult(true, [true], [true]),
            $this->subject->explain($matchers, [null])
        );
        $this->assertEquals(
            new MatcherResult(false, [false], [false]),
            $this->subject->explain($matchers, [])
        );
    }

    public function testExplainWithWildcardAfterValue()
    {
        $matchers = [
            $this->matcherFactory->equalTo('a'),
            new WildcardMatcher($this->matcherFactory->equalTo('b'), 0, -1),
        ];

        $this->assertEquals(
            new MatcherResult(true, [true, true], [true]),
            $this->subject->explain($matchers, ['a'])
        );
        $this->assertEquals(
            new MatcherResult(true, [true, true], [true, true]),
            $this->subject->explain($matchers, ['a', 'b'])
        );
        $this->assertEquals(
            new MatcherResult(true, [true, true], [true, true, true]),
            $this->subject->explain($matchers, ['a', 'b', 'b'])
        );
        $this->assertEquals(
            new MatcherResult(false, [true, true], [true, false]),
            $this->subject->explain($matchers, ['a', 'x'])
        );
        $this->assertEquals(
            new MatcherResult(false, [true, true], [true, true, false]),
            $this->subject->explain($matchers, ['a', 'b', 'x'])
        );
        $this->assertEquals(
            new MatcherResult(false, [true, true], [true, false, false]),
            $this->subject->explain($matchers, ['a', 'x', 'b'])
        );
        $this->assertEquals(
            new MatcherResult(false, [true, true], [true, false, false]),
            $this->subject->explain($matchers, ['a', 'x', 'x'])
        );
    }

    public function testExplainWithWildcardBeforeValue()
    {
        $matchers = [
            new WildcardMatcher($this->matcherFactory->equalTo('b'), 0, -1),
            $this->matcherFactory->equalTo('a'),
        ];

        $this->assertEquals(
            new MatcherResult(true, [true, true], [true]),
            $this->subject->explain($matchers, ['a'])
        );
        $this->assertEquals(
            new MatcherResult(true, [true, true], [true, true]),
            $this->subject->explain($matchers, ['b', 'a'])
        );
        $this->assertEquals(
            new MatcherResult(true, [true, true], [true, true, true]),
            $this->subject->explain($matchers, ['b', 'b', 'a'])
        );
        $this->assertEquals(
            new MatcherResult(false, [true, false], [false, false]),
            $this->subject->explain($matchers, ['x', 'a'])
        );
        $this->assertEquals(
            new MatcherResult(false, [true, false], [true, false, false]),
            $this->subject->explain($matchers, ['b', 'x', 'a'])
        );
        $this->assertEquals(
            new MatcherResult(false, [true, false], [false, false, false]),
            $this->subject->explain($matchers, ['x', 'b', 'a'])
        );
        $this->assertEquals(
            new MatcherResult(false, [true, false], [false, false, false]),
            $this->subject->explain($matchers, ['x', 'x', 'a'])
        );
    }

    public function testExplainWithWildcardBeforeValueGreedy()
    {
        $matchers = [
            new WildcardMatcher($this->matcherFactory->equalTo('a'), 0, -1),
            $this->matcherFactory->equalTo('a'),
        ];

        $this->assertEquals(
            new MatcherResult(false, [true, false], [true, true, false]),
            $this->subject->explain($matchers, ['a', 'a'])
        );
    }

    public function testExplainWithOnlyWildcard()
    {
        $matchers = [new WildcardMatcher($this->matcherFactory->equalTo('b'), 0, -1)];

        $this->assertEquals(
            new MatcherResult(true, [true], []),
            $this->subject->explain($matchers, [])
        );
        $this->assertEquals(
            new MatcherResult(true, [true], [true]),
            $this->subject->explain($matchers, ['b'])
        );
        $this->assertEquals(
            new MatcherResult(true, [true], [true, true]),
            $this->subject->explain($matchers, ['b', 'b'])
        );
        $this->assertEquals(
            new MatcherResult(false, [true], [false]),
            $this->subject->explain($matchers, ['x'])
        );
        $this->assertEquals(
            new MatcherResult(false, [true], [true, false]),
            $this->subject->explain($matchers, ['b', 'x'])
        );
        $this->assertEquals(
            new MatcherResult(false, [true], [false, false]),
            $this->subject->explain($matchers, ['x', 'b'])
        );
        $this->assertEquals(
            new MatcherResult(false, [true], [false, false]),
            $this->subject->explain($matchers, ['x', 'x'])
        );
    }

    public function testExplainWithWildcardMinimumArguments()
    {
        $matchers = [
            $this->matcherFactory->equalTo('a'),
            new WildcardMatcher($this->matcherFactory->equalTo('b'), 2, -1),
        ];

        $this->assertEquals(
            new MatcherResult(false, [true, false], [true, false]),
            $this->subject->explain($matchers, ['a'])
        );
        $this->assertEquals(
            new MatcherResult(false, [true, false], [true, true, false]),
            $this->subject->explain($matchers, ['a', 'b'])
        );
        $this->assertEquals(
            new MatcherResult(true, [true, true], [true, true, true]),
            $this->subject->explain($matchers, ['a', 'b', 'b'])
        );
        $this->assertEquals(
            new MatcherResult(true, [true, true], [true, true, true, true]),
            $this->subject->explain($matchers, ['a', 'b', 'b', 'b'])
        );
        $this->assertEquals(
            new MatcherResult(false, [true, false], [true, false, false]),
            $this->subject->explain($matchers, ['a', 'x'])
        );
        $this->assertEquals(
            new MatcherResult(false, [true, false], [true, true, false]),
            $this->subject->explain($matchers, ['a', 'b', 'x'])
        );
        $this->assertEquals(
            new MatcherResult(false, [true, false], [true, false, true]),
            $this->subject->explain($matchers, ['a', 'x', 'b'])
        );
        $this->assertEquals(
            new MatcherResult(false, [true, false], [true, false, false]),
            $this->subject->explain($matchers, ['a', 'x', 'x'])
        );
    }

    public function testExplainWithWildcardMaximumArguments()
    {
        $matchers = [
            $this->matcherFactory->equalTo('a'),
            new WildcardMatcher($this->matcherFactory->equalTo('b'), 0, 1),
        ];

        $this->assertEquals(
            new MatcherResult(true, [true, true], [true]),
            $this->subject->explain($matchers, ['a'])
        );
        $this->assertEquals(
            new MatcherResult(true, [true, true], [true, true]),
            $this->subject->explain($matchers, ['a', 'b'])
        );
        $this->assertEquals(
            new MatcherResult(false, [true, true], [true, true, false]),
            $this->subject->explain($matchers, ['a', 'b', 'b'])
        );
        $this->assertEquals(
            new MatcherResult(false, [true, true], [true, false]),
            $this->subject->explain($matchers, ['a', 'x'])
        );
        $this->assertEquals(
            new MatcherResult(false, [true, true], [true, true, false]),
            $this->subject->explain($matchers, ['a', 'b', 'x'])
        );
        $this->assertEquals(
            new MatcherResult(false, [true, true], [true, false, false]),
            $this->subject->explain($matchers, ['a', 'x', 'b'])
        );
        $this->assertEquals(
            new MatcherResult(false, [true, true], [true, false, false]),
            $this->subject->explain($matchers, ['a', 'x', 'x'])
        );
    }
}
