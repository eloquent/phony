<?php

declare(strict_types=1);

namespace Eloquent\Phony\Matcher;

use AllowDynamicProperties;
use Eloquent\Phony\Test\Facade\FacadeContainer;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

#[AllowDynamicProperties]
class MatcherVerifierTest extends TestCase
{
    protected function setUp(): void
    {
        $container = new FacadeContainer();
        $this->subject = $container->matcherVerifier;

        $this->matcherFactory = $container->matcherFactory;
    }

    public function matchesData()
    {
        //                               matchers      expected
        return [
            'Exact arguments'        => [[1, 2, 3],    true],
            'Empty'                  => [[],           false],
            'First arguments'        => [[1, 2],       false],
            'Single argument'        => [[1],          false],
            'Last arguments'         => [[2, 3],       false],
            'Last argument'          => [[3],          false],
            'Extra arguments'        => [[1, 2, 3, 4], false],
            'First argument differs' => [[4, 2, 3],    false],
            'Last argument differs'  => [[1, 2, 4],    false],
            'Unused argument'        => [[4],          false],
        ];
    }

    /**
     * @dataProvider matchesData
     */
    public function testMatches(array $matchers, bool $expected)
    {
        $parameterNames = ['a', 'b', 'c'];
        $positionalArguments = [1, 2, 3];
        $mixedArguments = [1, 2, 'c' => 3];
        $namedArguments = ['a' => 1, 'b' => 2, 'c' => 3];

        $positionalMatchers = [];
        $mixedMatchers = [];
        $namedMatchers = [];
        $extraCount = 0;

        foreach ($matchers as $position => $value) {
            $matcher = $this->matcherFactory->adapt($value);
            $parameterName = $parameterNames[$position] ?? 'x' . $extraCount++;

            $positionalMatchers[$position] = $matcher;
            $mixedMatchers[$position > 0 ? $parameterName : $position] = $matcher;
            $namedMatchers[$parameterName] = $matcher;
        }

        // positional matchers with positional arguments
        $this->assertSame(
            $expected,
            $this->subject->matches($positionalMatchers, $parameterNames, $positionalArguments)
        );

        // positional matchers with mixed arguments
        $this->assertSame($expected, $this->subject->matches($positionalMatchers, $parameterNames, $mixedArguments));

        // positional matchers with named arguments
        $this->assertSame($expected, $this->subject->matches($positionalMatchers, $parameterNames, $namedArguments));

        // mixed matchers with positional arguments
        $this->assertSame($expected, $this->subject->matches($mixedMatchers, $parameterNames, $positionalArguments));

        // mixed matchers with mixed arguments
        $this->assertSame($expected, $this->subject->matches($mixedMatchers, $parameterNames, $mixedArguments));

        // mixed matchers with named arguments
        $this->assertSame($expected, $this->subject->matches($mixedMatchers, $parameterNames, $namedArguments));

        // named matchers with positional arguments
        $this->assertSame($expected, $this->subject->matches($namedMatchers, $parameterNames, $positionalArguments));

        // named matchers with mixed arguments
        $this->assertSame($expected, $this->subject->matches($namedMatchers, $parameterNames, $mixedArguments));

        // named matchers with named arguments
        $this->assertSame($expected, $this->subject->matches($namedMatchers, $parameterNames, $namedArguments));
    }

    public function testMatchesExplicitArgumentExistence()
    {
        $parameterNames = ['a'];
        $positionalArguments = [null];
        $namedArguments = ['a' => null];

        $positionalMatchers = [$this->matcherFactory->equalTo(null)];
        $namedMatchers = ['a' => $this->matcherFactory->equalTo(null)];

        // positional matchers with empty arguments
        $this->assertFalse($this->subject->matches($positionalMatchers, $parameterNames, []));

        // positional matchers with positional arguments
        $this->assertTrue($this->subject->matches($positionalMatchers, $parameterNames, $positionalArguments));

        // positional matchers with named arguments
        $this->assertTrue($this->subject->matches($positionalMatchers, $parameterNames, $namedArguments));

        // named matchers with empty arguments
        $this->assertFalse($this->subject->matches($namedMatchers, $parameterNames, []));

        // named matchers with positional arguments
        $this->assertTrue($this->subject->matches($namedMatchers, $parameterNames, $positionalArguments));

        // named matchers with named arguments
        $this->assertTrue($this->subject->matches($namedMatchers, $parameterNames, $namedArguments));
    }

    public function testMatchesWithOnlyWildcard()
    {
        $parameterNames = ['a', 'b'];
        $wildcard = new WildcardMatcher($this->matcherFactory->equalTo(1), 0, -1);
        $matchers = [$wildcard];

        // empty arguments
        $this->assertTrue($this->subject->matches($matchers, $parameterNames, []));

        // positional arguments
        $this->assertTrue($this->subject->matches($matchers, $parameterNames, [1]));
        $this->assertTrue($this->subject->matches($matchers, $parameterNames, [1, 1]));
        $this->assertFalse($this->subject->matches($matchers, $parameterNames, [2]));
        $this->assertFalse($this->subject->matches($matchers, $parameterNames, [1, 2]));
        $this->assertFalse($this->subject->matches($matchers, $parameterNames, [2, 1]));
        $this->assertFalse($this->subject->matches($matchers, $parameterNames, [2, 2]));

        // mixed arguments
        $this->assertTrue($this->subject->matches($matchers, $parameterNames, [1, 'b' => 1]));
        $this->assertFalse($this->subject->matches($matchers, $parameterNames, [1, 'b' => 2]));
        $this->assertFalse($this->subject->matches($matchers, $parameterNames, [2, 'b' => 1]));
        $this->assertFalse($this->subject->matches($matchers, $parameterNames, [2, 'b' => 2]));

        // named arguments
        $this->assertTrue($this->subject->matches($matchers, $parameterNames, ['a' => 1]));
        $this->assertTrue($this->subject->matches($matchers, $parameterNames, ['a' => 1, 'b' => 1]));
        $this->assertFalse($this->subject->matches($matchers, $parameterNames, ['a' => 2]));
        $this->assertFalse($this->subject->matches($matchers, $parameterNames, ['a' => 1, 'b' => 2]));
        $this->assertFalse($this->subject->matches($matchers, $parameterNames, ['a' => 2, 'b' => 1]));
        $this->assertFalse($this->subject->matches($matchers, $parameterNames, ['a' => 2, 'b' => 2]));
    }

    public function testMatchesWithWildcardAfterValue()
    {
        $parameterNames = ['a', 'b'];
        $matcher1 = $this->matcherFactory->equalTo(1);
        $matcher2 = $this->matcherFactory->equalTo(2);
        $wildcard = new WildcardMatcher($this->matcherFactory->equalTo(3), 0, -1);
        $positionalMatchers = [$matcher1, $matcher2, $wildcard];
        $mixedMatchers = [$matcher1, 'b' => $matcher2, $wildcard];
        $namedMatchers = ['a' => $matcher1, 'b' => $matcher2, 'x' => $wildcard];

        $matchingPositionalArguments = [
            [1, 2],
            [1, 2, 3],
            [1, 2, 3, 3],
        ];
        $nonMatchingPositionalArguments = [
            [1, 2, 4],
            [1, 2, 3, 4],
            [1, 2, 4, 3],
            [1, 2, 4, 4],
        ];

        $matchingMixedArguments = [
            [1, 'b' => 2],
            [1, 'b' => 2, 'c' => 3],
            [1, 'b' => 2, 'c' => 3, 'd' => 3],
        ];
        $nonMatchingMixedArguments = [
            [1, 'b' => 2, 'c' => 4],
            [1, 'b' => 2, 'c' => 3, 'd' => 4],
            [1, 'b' => 2, 'c' => 4, 'd' => 3],
            [1, 'b' => 2, 'c' => 4, 'd' => 4],
        ];

        $matchingNamedArguments = [
            ['a' => 1, 'b' => 2],
            ['a' => 1, 'b' => 2, 'c' => 3],
            ['a' => 1, 'b' => 2, 'c' => 3, 'd' => 3],
        ];
        $nonMatchingNamedArguments = [
            ['a' => 1, 'b' => 2, 'c' => 4],
            ['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4],
            ['a' => 1, 'b' => 2, 'c' => 4, 'd' => 3],
            ['a' => 1, 'b' => 2, 'c' => 4, 'd' => 4],
        ];

        // positional matchers with positional arguments
        foreach ($matchingPositionalArguments as $arguments) {
            $this->assertTrue($this->subject->matches($positionalMatchers, $parameterNames, $arguments));
        }
        foreach ($nonMatchingPositionalArguments as $arguments) {
            $this->assertFalse($this->subject->matches($positionalMatchers, $parameterNames, $arguments));
        }

        // positional matchers with mixed arguments
        foreach ($matchingMixedArguments as $arguments) {
            $this->assertTrue($this->subject->matches($positionalMatchers, $parameterNames, $arguments));
        }
        foreach ($nonMatchingMixedArguments as $arguments) {
            $this->assertFalse($this->subject->matches($positionalMatchers, $parameterNames, $arguments));
        }

        // positional matchers with named arguments
        foreach ($matchingNamedArguments as $arguments) {
            $this->assertTrue($this->subject->matches($positionalMatchers, $parameterNames, $arguments));
        }
        foreach ($nonMatchingNamedArguments as $arguments) {
            $this->assertFalse($this->subject->matches($positionalMatchers, $parameterNames, $arguments));
        }

        // mixed matchers with positional arguments
        foreach ($matchingPositionalArguments as $arguments) {
            $this->assertTrue($this->subject->matches($mixedMatchers, $parameterNames, $arguments));
        }
        foreach ($nonMatchingPositionalArguments as $arguments) {
            $this->assertFalse($this->subject->matches($mixedMatchers, $parameterNames, $arguments));
        }

        // mixed matchers with mixed arguments
        foreach ($matchingMixedArguments as $arguments) {
            $this->assertTrue($this->subject->matches($mixedMatchers, $parameterNames, $arguments));
        }
        foreach ($nonMatchingMixedArguments as $arguments) {
            $this->assertFalse($this->subject->matches($mixedMatchers, $parameterNames, $arguments));
        }

        // mixed matchers with named arguments
        foreach ($matchingNamedArguments as $arguments) {
            $this->assertTrue($this->subject->matches($mixedMatchers, $parameterNames, $arguments));
        }
        foreach ($nonMatchingNamedArguments as $arguments) {
            $this->assertFalse($this->subject->matches($mixedMatchers, $parameterNames, $arguments));
        }

        // named matchers with positional arguments
        foreach ($matchingPositionalArguments as $arguments) {
            $this->assertTrue($this->subject->matches($namedMatchers, $parameterNames, $arguments));
        }
        foreach ($nonMatchingPositionalArguments as $arguments) {
            $this->assertFalse($this->subject->matches($namedMatchers, $parameterNames, $arguments));
        }

        // named matchers with mixed arguments
        foreach ($matchingMixedArguments as $arguments) {
            $this->assertTrue($this->subject->matches($namedMatchers, $parameterNames, $arguments));
        }
        foreach ($nonMatchingMixedArguments as $arguments) {
            $this->assertFalse($this->subject->matches($namedMatchers, $parameterNames, $arguments));
        }

        // named matchers with named arguments
        foreach ($matchingNamedArguments as $arguments) {
            $this->assertTrue($this->subject->matches($namedMatchers, $parameterNames, $arguments));
        }
        foreach ($nonMatchingNamedArguments as $arguments) {
            $this->assertFalse($this->subject->matches($namedMatchers, $parameterNames, $arguments));
        }
    }

    public function testMatchesWithWildcardMinimumArguments()
    {
        $parameterNames = ['a', 'b'];
        $matcher1 = $this->matcherFactory->equalTo(1);
        $matcher2 = $this->matcherFactory->equalTo(2);
        $wildcard = new WildcardMatcher($this->matcherFactory->equalTo(3), 1, -1);
        $positionalMatchers = [$matcher1, $matcher2, $wildcard];
        $mixedMatchers = [$matcher1, 'b' => $matcher2, $wildcard];
        $namedMatchers = ['a' => $matcher1, 'b' => $matcher2, 'x' => $wildcard];

        $matchingPositionalArguments = [
            [1, 2, 3],
            [1, 2, 3, 3],
        ];
        $nonMatchingPositionalArguments = [
            [1, 2],
            [1, 2, 4],
            [1, 2, 3, 4],
            [1, 2, 4, 3],
            [1, 2, 4, 4],
        ];

        $matchingMixedArguments = [
            [1, 'b' => 2, 'c' => 3],
            [1, 'b' => 2, 'c' => 3, 'd' => 3],
        ];
        $nonMatchingMixedArguments = [
            [1, 'b' => 2],
            [1, 'b' => 2, 'c' => 4],
            [1, 'b' => 2, 'c' => 3, 'd' => 4],
            [1, 'b' => 2, 'c' => 4, 'd' => 3],
            [1, 'b' => 2, 'c' => 4, 'd' => 4],
        ];

        $matchingNamedArguments = [
            ['a' => 1, 'b' => 2, 'c' => 3],
            ['a' => 1, 'b' => 2, 'c' => 3, 'd' => 3],
        ];
        $nonMatchingNamedArguments = [
            ['a' => 1, 'b' => 2],
            ['a' => 1, 'b' => 2, 'c' => 4],
            ['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4],
            ['a' => 1, 'b' => 2, 'c' => 4, 'd' => 3],
            ['a' => 1, 'b' => 2, 'c' => 4, 'd' => 4],
        ];

        // positional matchers with positional arguments
        foreach ($matchingPositionalArguments as $arguments) {
            $this->assertTrue($this->subject->matches($positionalMatchers, $parameterNames, $arguments));
        }
        foreach ($nonMatchingPositionalArguments as $arguments) {
            $this->assertFalse($this->subject->matches($positionalMatchers, $parameterNames, $arguments));
        }

        // positional matchers with mixed arguments
        foreach ($matchingMixedArguments as $arguments) {
            $this->assertTrue($this->subject->matches($positionalMatchers, $parameterNames, $arguments));
        }
        foreach ($nonMatchingMixedArguments as $arguments) {
            $this->assertFalse($this->subject->matches($positionalMatchers, $parameterNames, $arguments));
        }

        // positional matchers with named arguments
        foreach ($matchingNamedArguments as $arguments) {
            $this->assertTrue($this->subject->matches($positionalMatchers, $parameterNames, $arguments));
        }
        foreach ($nonMatchingNamedArguments as $arguments) {
            $this->assertFalse($this->subject->matches($positionalMatchers, $parameterNames, $arguments));
        }

        // mixed matchers with positional arguments
        foreach ($matchingPositionalArguments as $arguments) {
            $this->assertTrue($this->subject->matches($mixedMatchers, $parameterNames, $arguments));
        }
        foreach ($nonMatchingPositionalArguments as $arguments) {
            $this->assertFalse($this->subject->matches($mixedMatchers, $parameterNames, $arguments));
        }

        // mixed matchers with mixed arguments
        foreach ($matchingMixedArguments as $arguments) {
            $this->assertTrue($this->subject->matches($mixedMatchers, $parameterNames, $arguments));
        }
        foreach ($nonMatchingMixedArguments as $arguments) {
            $this->assertFalse($this->subject->matches($mixedMatchers, $parameterNames, $arguments));
        }

        // mixed matchers with named arguments
        foreach ($matchingNamedArguments as $arguments) {
            $this->assertTrue($this->subject->matches($mixedMatchers, $parameterNames, $arguments));
        }
        foreach ($nonMatchingNamedArguments as $arguments) {
            $this->assertFalse($this->subject->matches($mixedMatchers, $parameterNames, $arguments));
        }

        // named matchers with positional arguments
        foreach ($matchingPositionalArguments as $arguments) {
            $this->assertTrue($this->subject->matches($namedMatchers, $parameterNames, $arguments));
        }
        foreach ($nonMatchingPositionalArguments as $arguments) {
            $this->assertFalse($this->subject->matches($namedMatchers, $parameterNames, $arguments));
        }

        // named matchers with mixed arguments
        foreach ($matchingMixedArguments as $arguments) {
            $this->assertTrue($this->subject->matches($namedMatchers, $parameterNames, $arguments));
        }
        foreach ($nonMatchingMixedArguments as $arguments) {
            $this->assertFalse($this->subject->matches($namedMatchers, $parameterNames, $arguments));
        }

        // named matchers with named arguments
        foreach ($matchingNamedArguments as $arguments) {
            $this->assertTrue($this->subject->matches($namedMatchers, $parameterNames, $arguments));
        }
        foreach ($nonMatchingNamedArguments as $arguments) {
            $this->assertFalse($this->subject->matches($namedMatchers, $parameterNames, $arguments));
        }
    }

    public function testMatchesWithWildcardMaximumArguments()
    {
        $parameterNames = ['a', 'b'];
        $matcher1 = $this->matcherFactory->equalTo(1);
        $matcher2 = $this->matcherFactory->equalTo(2);
        $wildcard = new WildcardMatcher($this->matcherFactory->equalTo(3), 0, 1);
        $positionalMatchers = [$matcher1, $matcher2, $wildcard];
        $mixedMatchers = [$matcher1, 'b' => $matcher2, $wildcard];
        $namedMatchers = ['a' => $matcher1, 'b' => $matcher2, 'x' => $wildcard];

        $matchingPositionalArguments = [
            [1, 2],
            [1, 2, 3],
        ];
        $nonMatchingPositionalArguments = [
            [1, 2, 4],
            [1, 2, 3, 3],
            [1, 2, 3, 4],
            [1, 2, 4, 3],
            [1, 2, 4, 4],
        ];

        $matchingMixedArguments = [
            [1, 'b' => 2],
            [1, 'b' => 2, 'c' => 3],
        ];
        $nonMatchingMixedArguments = [
            [1, 'b' => 2, 'c' => 4],
            [1, 'b' => 2, 'c' => 3, 'd' => 3],
            [1, 'b' => 2, 'c' => 3, 'd' => 4],
            [1, 'b' => 2, 'c' => 4, 'd' => 3],
            [1, 'b' => 2, 'c' => 4, 'd' => 4],
        ];

        $matchingNamedArguments = [
            ['a' => 1, 'b' => 2],
            ['a' => 1, 'b' => 2, 'c' => 3],
        ];
        $nonMatchingNamedArguments = [
            ['a' => 1, 'b' => 2, 'c' => 4],
            ['a' => 1, 'b' => 2, 'c' => 3, 'd' => 3],
            ['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4],
            ['a' => 1, 'b' => 2, 'c' => 4, 'd' => 3],
            ['a' => 1, 'b' => 2, 'c' => 4, 'd' => 4],
        ];

        // positional matchers with positional arguments
        foreach ($matchingPositionalArguments as $arguments) {
            $this->assertTrue($this->subject->matches($positionalMatchers, $parameterNames, $arguments));
        }
        foreach ($nonMatchingPositionalArguments as $arguments) {
            $this->assertFalse($this->subject->matches($positionalMatchers, $parameterNames, $arguments));
        }

        // positional matchers with mixed arguments
        foreach ($matchingMixedArguments as $arguments) {
            $this->assertTrue($this->subject->matches($positionalMatchers, $parameterNames, $arguments));
        }
        foreach ($nonMatchingMixedArguments as $arguments) {
            $this->assertFalse($this->subject->matches($positionalMatchers, $parameterNames, $arguments));
        }

        // positional matchers with named arguments
        foreach ($matchingNamedArguments as $arguments) {
            $this->assertTrue($this->subject->matches($positionalMatchers, $parameterNames, $arguments));
        }
        foreach ($nonMatchingNamedArguments as $arguments) {
            $this->assertFalse($this->subject->matches($positionalMatchers, $parameterNames, $arguments));
        }

        // mixed matchers with positional arguments
        foreach ($matchingPositionalArguments as $arguments) {
            $this->assertTrue($this->subject->matches($mixedMatchers, $parameterNames, $arguments));
        }
        foreach ($nonMatchingPositionalArguments as $arguments) {
            $this->assertFalse($this->subject->matches($mixedMatchers, $parameterNames, $arguments));
        }

        // mixed matchers with mixed arguments
        foreach ($matchingMixedArguments as $arguments) {
            $this->assertTrue($this->subject->matches($mixedMatchers, $parameterNames, $arguments));
        }
        foreach ($nonMatchingMixedArguments as $arguments) {
            $this->assertFalse($this->subject->matches($mixedMatchers, $parameterNames, $arguments));
        }

        // mixed matchers with named arguments
        foreach ($matchingNamedArguments as $arguments) {
            $this->assertTrue($this->subject->matches($mixedMatchers, $parameterNames, $arguments));
        }
        foreach ($nonMatchingNamedArguments as $arguments) {
            $this->assertFalse($this->subject->matches($mixedMatchers, $parameterNames, $arguments));
        }

        // named matchers with positional arguments
        foreach ($matchingPositionalArguments as $arguments) {
            $this->assertTrue($this->subject->matches($namedMatchers, $parameterNames, $arguments));
        }
        foreach ($nonMatchingPositionalArguments as $arguments) {
            $this->assertFalse($this->subject->matches($namedMatchers, $parameterNames, $arguments));
        }

        // named matchers with mixed arguments
        foreach ($matchingMixedArguments as $arguments) {
            $this->assertTrue($this->subject->matches($namedMatchers, $parameterNames, $arguments));
        }
        foreach ($nonMatchingMixedArguments as $arguments) {
            $this->assertFalse($this->subject->matches($namedMatchers, $parameterNames, $arguments));
        }

        // named matchers with named arguments
        foreach ($matchingNamedArguments as $arguments) {
            $this->assertTrue($this->subject->matches($namedMatchers, $parameterNames, $arguments));
        }
        foreach ($nonMatchingNamedArguments as $arguments) {
            $this->assertFalse($this->subject->matches($namedMatchers, $parameterNames, $arguments));
        }
    }

    public function testMatchesFailureWithWildcardBeforeValue()
    {
        $matchers = [
            new WildcardMatcher($this->matcherFactory->equalTo(2), 0, -1),
            $this->matcherFactory->equalTo(1),
        ];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Wildcard matchers cannot be followed by other matchers.');
        $this->subject->matches($matchers, [], [1]);
    }

    public function explainData()
    {
        //                               matchers      isMatch matcherMatches             argumentMatches
        return [
            'Exact arguments'        => [[1, 2, 3],    true,   [true, true, true],        [true, true, true]],
            'Empty'                  => [[],           false,  [],                        [false, false, false]],
            'First arguments'        => [[1, 2],       false,  [true, true],              [true, true, false]],
            'Single argument'        => [[1],          false,  [true],                    [true, false, false]],
            'Last arguments'         => [[2, 3],       false,  [false, false],            [false, false, false]],
            'Last argument'          => [[3],          false,  [false],                   [false, false, false]],
            'Extra arguments'        => [[1, 2, 3, 4], false,  [true, true, true, false], [true, true, true, false]],
            'First argument differs' => [[4, 2, 3],    false,  [false, true, true],       [false, true, true]],
            'Last argument differs'  => [[1, 2, 4],    false,  [true, true, false],       [true, true, false]],
            'Unused argument'        => [[4],          false,  [false],                   [false, false, false]],
        ];
    }

    /**
     * @dataProvider explainData
     */
    public function testExplain(array $matchers, bool $isMatch, array $matcherMatches, array $argumentMatches)
    {
        $parameterNames = ['a', 'b', 'c'];
        $positionalArguments = [1, 2, 3];
        $mixedArguments = [1, 2, 'c' => 3];
        $namedArguments = ['a' => 1, 'b' => 2, 'c' => 3];

        $positionalArgumentMatches = [];
        $mixedArgumentMatches = [];
        $namedArgumentMatches = [];

        foreach ($argumentMatches as $position => $argumentIsMatch) {
            $parameterName = $parameterNames[$position] ?? $position;

            $positionalArgumentMatches[$position] = $argumentIsMatch;
            $mixedArgumentMatches[$position > 1 ? $parameterName : $position] = $argumentIsMatch;
            $namedArgumentMatches[$parameterName] = $argumentIsMatch;
        }

        $positionalMatchers = [];
        $positionalMatcherMatches = [];
        $mixedMatchers = [];
        $mixedMatcherMatches = [];
        $namedMatchers = [];
        $namedMatcherMatches = [];
        $extraCount = 0;

        foreach ($matchers as $position => $value) {
            $matcher = $this->matcherFactory->adapt($value);
            $parameterName = $parameterNames[$position] ?? 'x' . $extraCount++;

            $positionalMatchers[$position] = $matcher;
            $positionalMatcherMatches[$position] = $matcherMatches[$position];
            $mixedMatchers[$position > 0 ? $parameterName : $position] = $matcher;
            $mixedMatcherMatches[$position > 0 ? $parameterName : $position] = $matcherMatches[$position];
            $namedMatchers[$parameterName] = $matcher;
            $namedMatcherMatches[$parameterName] = $matcherMatches[$position];
        }

        // positional matchers with positional arguments
        $this->assertEquals(
            new MatcherResult($isMatch, $positionalMatcherMatches, $positionalArgumentMatches),
            $this->subject->explain($positionalMatchers, $parameterNames, $positionalArguments)
        );

        // positional matchers with mixed arguments
        $this->assertEquals(
            new MatcherResult($isMatch, $positionalMatcherMatches, $mixedArgumentMatches),
            $this->subject->explain($positionalMatchers, $parameterNames, $mixedArguments)
        );

        // positional matchers with named arguments
        $this->assertEquals(
            new MatcherResult($isMatch, $positionalMatcherMatches, $namedArgumentMatches),
            $this->subject->explain($positionalMatchers, $parameterNames, $namedArguments)
        );

        // mixed matchers with positional arguments
        $this->assertEquals(
            new MatcherResult($isMatch, $mixedMatcherMatches, $positionalArgumentMatches),
            $this->subject->explain($mixedMatchers, $parameterNames, $positionalArguments)
        );

        // mixed matchers with mixed arguments
        $this->assertEquals(
            new MatcherResult($isMatch, $mixedMatcherMatches, $mixedArgumentMatches),
            $this->subject->explain($mixedMatchers, $parameterNames, $mixedArguments)
        );

        // mixed matchers with named arguments
        $this->assertEquals(
            new MatcherResult($isMatch, $mixedMatcherMatches, $namedArgumentMatches),
            $this->subject->explain($mixedMatchers, $parameterNames, $namedArguments)
        );

        // named matchers with positional arguments
        $this->assertEquals(
            new MatcherResult($isMatch, $namedMatcherMatches, $positionalArgumentMatches),
            $this->subject->explain($namedMatchers, $parameterNames, $positionalArguments)
        );

        // named matchers with mixed arguments
        $this->assertEquals(
            new MatcherResult($isMatch, $namedMatcherMatches, $mixedArgumentMatches),
            $this->subject->explain($namedMatchers, $parameterNames, $mixedArguments)
        );

        // named matchers with named arguments
        $this->assertEquals(
            new MatcherResult($isMatch, $namedMatcherMatches, $namedArgumentMatches),
            $this->subject->explain($namedMatchers, $parameterNames, $namedArguments)
        );
    }

    public function testExplainExplicitArgumentExistence()
    {
        $parameterNames = ['a'];
        $positionalArguments = [null];
        $namedArguments = ['a' => null];

        $positionalMatchers = [$this->matcherFactory->equalTo(null)];
        $namedMatchers = ['a' => $this->matcherFactory->equalTo(null)];

        // positional matchers with empty arguments
        $this->assertEquals(
            new MatcherResult(false, [false], [false]),
            $this->subject->explain($positionalMatchers, $parameterNames, [])
        );

        // positional matchers with positional arguments
        $this->assertEquals(
            new MatcherResult(true, [true], [true]),
            $this->subject->explain($positionalMatchers, $parameterNames, $positionalArguments)
        );

        // positional matchers with named arguments
        $this->assertEquals(
            new MatcherResult(true, [true], ['a' => true]),
            $this->subject->explain($positionalMatchers, $parameterNames, $namedArguments)
        );

        // named matchers with empty arguments
        $this->assertEquals(
            new MatcherResult(false, ['a' => false], [false]),
            $this->subject->explain($namedMatchers, $parameterNames, [])
        );

        // named matchers with positional arguments
        $this->assertEquals(
            new MatcherResult(true, ['a' => true], [true]),
            $this->subject->explain($namedMatchers, $parameterNames, $positionalArguments)
        );

        // named matchers with named arguments
        $this->assertEquals(
            new MatcherResult(true, ['a' => true], ['a' => true]),
            $this->subject->explain($namedMatchers, $parameterNames, $namedArguments)
        );
    }

    public function testExplainWithOnlyWildcard()
    {
        $parameterNames = ['a', 'b'];
        $wildcard = new WildcardMatcher($this->matcherFactory->equalTo(1), 0, -1);
        $matchers = [$wildcard];

        // empty arguments
        $this->assertEquals(
            new MatcherResult(true, [true], []),
            $this->subject->explain($matchers, $parameterNames, [])
        );

        // positional arguments
        $this->assertEquals(
            new MatcherResult(true, [true], [true]),
            $this->subject->explain($matchers, $parameterNames, [1])
        );
        $this->assertEquals(
            new MatcherResult(true, [true], [true, true]),
            $this->subject->explain($matchers, $parameterNames, [1, 1])
        );
        $this->assertEquals(
            new MatcherResult(false, [true], [false]),
            $this->subject->explain($matchers, $parameterNames, [2])
        );
        $this->assertEquals(
            new MatcherResult(false, [true], [true, false]),
            $this->subject->explain($matchers, $parameterNames, [1, 2])
        );
        $this->assertEquals(
            new MatcherResult(false, [true], [false, false]),
            $this->subject->explain($matchers, $parameterNames, [2, 1])
        );
        $this->assertEquals(
            new MatcherResult(false, [true], [false, false]),
            $this->subject->explain($matchers, $parameterNames, [2, 2])
        );

        // mixed arguments
        $this->assertEquals(
            new MatcherResult(true, [true], [true, 'b' => true]),
            $this->subject->explain($matchers, $parameterNames, [1, 'b' => 1])
        );
        $this->assertEquals(
            new MatcherResult(false, [true], [true, 'b' => false]),
            $this->subject->explain($matchers, $parameterNames, [1, 'b' => 2])
        );
        $this->assertEquals(
            new MatcherResult(false, [true], [false, 'b' => false]),
            $this->subject->explain($matchers, $parameterNames, [2, 'b' => 1])
        );
        $this->assertEquals(
            new MatcherResult(false, [true], [false, 'b' => false]),
            $this->subject->explain($matchers, $parameterNames, [2, 'b' => 2])
        );

        // named arguments
        $this->assertEquals(
            new MatcherResult(true, [true], ['a' => true]),
            $this->subject->explain($matchers, $parameterNames, ['a' => 1])
        );
        $this->assertEquals(
            new MatcherResult(true, [true], ['a' => true, 'b' => true]),
            $this->subject->explain($matchers, $parameterNames, ['a' => 1, 'b' => 1])
        );
        $this->assertEquals(
            new MatcherResult(false, [true], ['a' => false]),
            $this->subject->explain($matchers, $parameterNames, ['a' => 2])
        );
        $this->assertEquals(
            new MatcherResult(false, [true], ['a' => true, 'b' => false]),
            $this->subject->explain($matchers, $parameterNames, ['a' => 1, 'b' => 2])
        );
        $this->assertEquals(
            new MatcherResult(false, [true], ['a' => false, 'b' => false]),
            $this->subject->explain($matchers, $parameterNames, ['a' => 2, 'b' => 1])
        );
        $this->assertEquals(
            new MatcherResult(false, [true], ['a' => false, 'b' => false]),
            $this->subject->explain($matchers, $parameterNames, ['a' => 2, 'b' => 2])
        );
    }

    public function testExplainWithWildcardAfterValue()
    {
        $parameterNames = ['a', 'b'];
        $matcher1 = $this->matcherFactory->equalTo(1);
        $matcher2 = $this->matcherFactory->equalTo(2);
        $wildcard = new WildcardMatcher($this->matcherFactory->equalTo(3), 0, -1);
        $positionalMatchers = [$matcher1, $matcher2, $wildcard];
        $mixedMatchers = [$matcher1, 'b' => $matcher2, $wildcard];
        $namedMatchers = ['a' => $matcher1, 'b' => $matcher2, 'x' => $wildcard];

        $matchingPositionalArguments = [
            [
                [1, 2],
                [true, true, true],
                [true, true],
            ],
            [
                [1, 2, 3],
                [true, true, true],
                [true, true, true],
            ],
            [
                [1, 2, 3, 3],
                [true, true, true],
                [true, true, true, true],
            ],
        ];
        $nonMatchingPositionalArguments = [
            [
                [1, 2, 4],
                [true, true, true],
                [true, true, false],
            ],
            [
                [1, 2, 3, 4],
                [true, true, true],
                [true, true, true, false],
            ],
            [
                [1, 2, 4, 3],
                [true, true, true],
                [true, true, false, false],
            ],
            [
                [1, 2, 4, 4],
                [true, true, true],
                [true, true, false, false],
            ],
        ];

        $matchingMixedArguments = [
            [
                [1, 'b' => 2],
                [true, true, true],
                [true, 'b' => true],
            ],
            [
                [1, 'b' => 2, 'c' => 3],
                [true, true, true],
                [true, 'b' => true, 'c' => true],
            ],
            [
                [1, 'b' => 2, 'c' => 3, 'd' => 3],
                [true, true, true],
                [true, 'b' => true, 'c' => true, 'd' => true],
            ],
        ];
        $nonMatchingMixedArguments = [
            [
                [1, 'b' => 2, 'c' => 4],
                [true, true, true],
                [true, 'b' => true, 'c' => false],
            ],
            [
                [1, 'b' => 2, 'c' => 3, 'd' => 4],
                [true, true, true],
                [true, 'b' => true, 'c' => true, 'd' => false],
            ],
            [
                [1, 'b' => 2, 'c' => 4, 'd' => 3],
                [true, true, true],
                [true, 'b' => true, 'c' => false, 'd' => false],
            ],
            [
                [1, 'b' => 2, 'c' => 4, 'd' => 4],
                [true, true, true],
                [true, 'b' => true, 'c' => false, 'd' => false],
            ],
        ];

        $matchingNamedArguments = [
            [
                ['a' => 1, 'b' => 2],
                [true, true, true],
                ['a' => true, 'b' => true],
            ],
            [
                ['a' => 1, 'b' => 2, 'c' => 3],
                [true, true, true],
                ['a' => true, 'b' => true, 'c' => true],
            ],
            [
                ['a' => 1, 'b' => 2, 'c' => 3, 'd' => 3],
                [true, true, true],
                ['a' => true, 'b' => true, 'c' => true, 'd' => true],
            ],
        ];
        $nonMatchingNamedArguments = [
            [
                ['a' => 1, 'b' => 2, 'c' => 4],
                [true, true, true],
                ['a' => true, 'b' => true, 'c' => false],
            ],
            [
                ['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4],
                [true, true, true],
                ['a' => true, 'b' => true, 'c' => true, 'd' => false],
            ],
            [
                ['a' => 1, 'b' => 2, 'c' => 4, 'd' => 3],
                [true, true, true],
                ['a' => true, 'b' => true, 'c' => false, 'd' => false],
            ],
            [
                ['a' => 1, 'b' => 2, 'c' => 4, 'd' => 4],
                [true, true, true],
                ['a' => true, 'b' => true, 'c' => false, 'd' => false],
            ],
        ];

        // positional matchers with positional arguments
        foreach ($matchingPositionalArguments as $i => list($arguments, $matcherMatches, $argumentMatches)) {
            $this->assertEquals(
                new MatcherResult(true, $matcherMatches, $argumentMatches),
                $this->subject->explain($positionalMatchers, $parameterNames, $arguments),
                "Positional matchers with positional arguments matching #$i"
            );
        }
        foreach ($nonMatchingPositionalArguments as $i => list($arguments, $matcherMatches, $argumentMatches)) {
            $this->assertEquals(
                new MatcherResult(false, $matcherMatches, $argumentMatches),
                $this->subject->explain($positionalMatchers, $parameterNames, $arguments),
                "Positional matchers with positional arguments non-matching #$i"
            );
        }

        // positional matchers with mixed arguments
        foreach ($matchingMixedArguments as $i => list($arguments, $matcherMatches, $argumentMatches)) {
            $this->assertEquals(
                new MatcherResult(true, $matcherMatches, $argumentMatches),
                $this->subject->explain($positionalMatchers, $parameterNames, $arguments),
                "Positional matchers with mixed arguments matching #$i"
            );
        }
        foreach ($nonMatchingMixedArguments as $i => list($arguments, $matcherMatches, $argumentMatches)) {
            $this->assertEquals(
                new MatcherResult(false, $matcherMatches, $argumentMatches),
                $this->subject->explain($positionalMatchers, $parameterNames, $arguments),
                "Positional matchers with mixed arguments non-matching #$i"
            );
        }

        // positional matchers with named arguments
        foreach ($matchingNamedArguments as $i => list($arguments, $matcherMatches, $argumentMatches)) {
            $this->assertEquals(
                new MatcherResult(true, $matcherMatches, $argumentMatches),
                $this->subject->explain($positionalMatchers, $parameterNames, $arguments),
                "Positional matchers with named arguments matching #$i"
            );
        }
        foreach ($nonMatchingNamedArguments as $i => list($arguments, $matcherMatches, $argumentMatches)) {
            $this->assertEquals(
                new MatcherResult(false, $matcherMatches, $argumentMatches),
                $this->subject->explain($positionalMatchers, $parameterNames, $arguments),
                "Positional matchers with named arguments non-matching #$i"
            );
        }

        // mixed matchers with positional arguments
        foreach ($matchingPositionalArguments as $i => list($arguments, $matcherMatches, $argumentMatches)) {
            $matcherMatches = array_combine(array_keys($mixedMatchers), $matcherMatches);
            $this->assertEquals(
                new MatcherResult(true, $matcherMatches, $argumentMatches),
                $this->subject->explain($mixedMatchers, $parameterNames, $arguments),
                "Mixed matchers with positional arguments matching #$i"
            );
        }
        foreach ($nonMatchingPositionalArguments as $i => list($arguments, $matcherMatches, $argumentMatches)) {
            $matcherMatches = array_combine(array_keys($mixedMatchers), $matcherMatches);
            $this->assertEquals(
                new MatcherResult(false, $matcherMatches, $argumentMatches),
                $this->subject->explain($mixedMatchers, $parameterNames, $arguments),
                "Mixed matchers with positional arguments non-matching #$i"
            );
        }

        // mixed matchers with mixed arguments
        foreach ($matchingMixedArguments as $i => list($arguments, $matcherMatches, $argumentMatches)) {
            $matcherMatches = array_combine(array_keys($mixedMatchers), $matcherMatches);
            $this->assertEquals(
                new MatcherResult(true, $matcherMatches, $argumentMatches),
                $this->subject->explain($mixedMatchers, $parameterNames, $arguments),
                "Mixed matchers with mixed arguments matching #$i"
            );
        }
        foreach ($nonMatchingMixedArguments as $i => list($arguments, $matcherMatches, $argumentMatches)) {
            $matcherMatches = array_combine(array_keys($mixedMatchers), $matcherMatches);
            $this->assertEquals(
                new MatcherResult(false, $matcherMatches, $argumentMatches),
                $this->subject->explain($mixedMatchers, $parameterNames, $arguments),
                "Mixed matchers with mixed arguments non-matching #$i"
            );
        }

        // mixed matchers with named arguments
        foreach ($matchingNamedArguments as $i => list($arguments, $matcherMatches, $argumentMatches)) {
            $matcherMatches = array_combine(array_keys($mixedMatchers), $matcherMatches);
            $this->assertEquals(
                new MatcherResult(true, $matcherMatches, $argumentMatches),
                $this->subject->explain($mixedMatchers, $parameterNames, $arguments),
                "Mixed matchers with named arguments matching #$i"
            );
        }
        foreach ($nonMatchingNamedArguments as $i => list($arguments, $matcherMatches, $argumentMatches)) {
            $matcherMatches = array_combine(array_keys($mixedMatchers), $matcherMatches);
            $this->assertEquals(
                new MatcherResult(false, $matcherMatches, $argumentMatches),
                $this->subject->explain($mixedMatchers, $parameterNames, $arguments),
                "Mixed matchers with named arguments non-matching #$i"
            );
        }

        // named matchers with positional arguments
        foreach ($matchingPositionalArguments as $i => list($arguments, $matcherMatches, $argumentMatches)) {
            $matcherMatches = array_combine(array_keys($namedMatchers), $matcherMatches);
            $this->assertEquals(
                new MatcherResult(true, $matcherMatches, $argumentMatches),
                $this->subject->explain($namedMatchers, $parameterNames, $arguments),
                "Named matchers with positional arguments matching #$i"
            );
        }
        foreach ($nonMatchingPositionalArguments as $i => list($arguments, $matcherMatches, $argumentMatches)) {
            $matcherMatches = array_combine(array_keys($namedMatchers), $matcherMatches);
            $this->assertEquals(
                new MatcherResult(false, $matcherMatches, $argumentMatches),
                $this->subject->explain($namedMatchers, $parameterNames, $arguments),
                "Named matchers with positional arguments non-matching #$i"
            );
        }

        // named matchers with mixed arguments
        foreach ($matchingMixedArguments as $i => list($arguments, $matcherMatches, $argumentMatches)) {
            $matcherMatches = array_combine(array_keys($namedMatchers), $matcherMatches);
            $this->assertEquals(
                new MatcherResult(true, $matcherMatches, $argumentMatches),
                $this->subject->explain($namedMatchers, $parameterNames, $arguments),
                "Named matchers with mixed arguments matching #$i"
            );
        }
        foreach ($nonMatchingMixedArguments as $i => list($arguments, $matcherMatches, $argumentMatches)) {
            $matcherMatches = array_combine(array_keys($namedMatchers), $matcherMatches);
            $this->assertEquals(
                new MatcherResult(false, $matcherMatches, $argumentMatches),
                $this->subject->explain($namedMatchers, $parameterNames, $arguments),
                "Named matchers with mixed arguments non-matching #$i"
            );
        }

        // named matchers with named arguments
        foreach ($matchingNamedArguments as $i => list($arguments, $matcherMatches, $argumentMatches)) {
            $matcherMatches = array_combine(array_keys($namedMatchers), $matcherMatches);
            $this->assertEquals(
                new MatcherResult(true, $matcherMatches, $argumentMatches),
                $this->subject->explain($namedMatchers, $parameterNames, $arguments),
                "Named matchers with named arguments matching #$i"
            );
        }
        foreach ($nonMatchingNamedArguments as $i => list($arguments, $matcherMatches, $argumentMatches)) {
            $matcherMatches = array_combine(array_keys($namedMatchers), $matcherMatches);
            $this->assertEquals(
                new MatcherResult(false, $matcherMatches, $argumentMatches),
                $this->subject->explain($namedMatchers, $parameterNames, $arguments),
                "Named matchers with named arguments non-matching #$i"
            );
        }
    }

    public function testExplainWithWildcardMinimumArguments()
    {
        $parameterNames = ['a', 'b'];
        $matcher1 = $this->matcherFactory->equalTo(1);
        $matcher2 = $this->matcherFactory->equalTo(2);
        $wildcard = new WildcardMatcher($this->matcherFactory->equalTo(3), 1, -1);
        $positionalMatchers = [$matcher1, $matcher2, $wildcard];
        $mixedMatchers = [$matcher1, 'b' => $matcher2, $wildcard];
        $namedMatchers = ['a' => $matcher1, 'b' => $matcher2, 'x' => $wildcard];

        $matchingPositionalArguments = [
            [
                [1, 2, 3],
                [true, true, true],
                [true, true, true],
            ],
            [
                [1, 2, 3, 3],
                [true, true, true],
                [true, true, true, true],
            ],
        ];
        $nonMatchingPositionalArguments = [
            [
                [1, 2],
                [true, true, false],
                [true, true, false],
            ],
            [
                [1, 2, 4],
                [true, true, false],
                [true, true, false],
            ],
            [
                [1, 2, 3, 4],
                [true, true, true],
                [true, true, true, false],
            ],
            [
                [1, 2, 4, 3],
                [true, true, false],
                [true, true, false, true],
            ],
            [
                [1, 2, 4, 4],
                [true, true, false],
                [true, true, false, false],
            ],
        ];

        $matchingMixedArguments = [
            [
                [1, 'b' => 2, 'c' => 3],
                [true, true, true],
                [true, 'b' => true, 'c' => true],
            ],
            [
                [1, 'b' => 2, 'c' => 3, 'd' => 3],
                [true, true, true],
                [true, 'b' => true, 'c' => true, 'd' => true],
            ],
        ];
        $nonMatchingMixedArguments = [
            [
                [1, 'b' => 2],
                [true, true, false],
                [true, 'b' => true, 2 => false],
            ],
            [
                [1, 'b' => 2, 'c' => 4],
                [true, true, false],
                [true, 'b' => true, 'c' => false],
            ],
            [
                [1, 'b' => 2, 'c' => 3, 'd' => 4],
                [true, true, true],
                [true, 'b' => true, 'c' => true, 'd' => false],
            ],
            [
                [1, 'b' => 2, 'c' => 4, 'd' => 3],
                [true, true, false],
                [true, 'b' => true, 'c' => false, 'd' => true],
            ],
            [
                [1, 'b' => 2, 'c' => 4, 'd' => 4],
                [true, true, false],
                [true, 'b' => true, 'c' => false, 'd' => false],
            ],
        ];

        $matchingNamedArguments = [
            [
                ['a' => 1, 'b' => 2, 'c' => 3],
                [true, true, true],
                ['a' => true, 'b' => true, 'c' => true],
            ],
            [
                ['a' => 1, 'b' => 2, 'c' => 3, 'd' => 3],
                [true, true, true],
                ['a' => true, 'b' => true, 'c' => true, 'd' => true],
            ],
        ];
        $nonMatchingNamedArguments = [
            [
                ['a' => 1, 'b' => 2],
                [true, true, false],
                ['a' => true, 'b' => true, 2 => false],
            ],
            [
                ['a' => 1, 'b' => 2, 'c' => 4],
                [true, true, false],
                ['a' => true, 'b' => true, 'c' => false],
            ],
            [
                ['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4],
                [true, true, true],
                ['a' => true, 'b' => true, 'c' => true, 'd' => false],
            ],
            [
                ['a' => 1, 'b' => 2, 'c' => 4, 'd' => 3],
                [true, true, false],
                ['a' => true, 'b' => true, 'c' => false, 'd' => true],
            ],
            [
                ['a' => 1, 'b' => 2, 'c' => 4, 'd' => 4],
                [true, true, false],
                ['a' => true, 'b' => true, 'c' => false, 'd' => false],
            ],
        ];

        // positional matchers with positional arguments
        foreach ($matchingPositionalArguments as $i => list($arguments, $matcherMatches, $argumentMatches)) {
            $this->assertEquals(
                new MatcherResult(true, $matcherMatches, $argumentMatches),
                $this->subject->explain($positionalMatchers, $parameterNames, $arguments),
                "Positional matchers with positional arguments matching #$i"
            );
        }
        foreach ($nonMatchingPositionalArguments as $i => list($arguments, $matcherMatches, $argumentMatches)) {
            $this->assertEquals(
                new MatcherResult(false, $matcherMatches, $argumentMatches),
                $this->subject->explain($positionalMatchers, $parameterNames, $arguments),
                "Positional matchers with positional arguments non-matching #$i"
            );
        }

        // positional matchers with mixed arguments
        foreach ($matchingMixedArguments as $i => list($arguments, $matcherMatches, $argumentMatches)) {
            $this->assertEquals(
                new MatcherResult(true, $matcherMatches, $argumentMatches),
                $this->subject->explain($positionalMatchers, $parameterNames, $arguments),
                "Positional matchers with mixed arguments matching #$i"
            );
        }
        foreach ($nonMatchingMixedArguments as $i => list($arguments, $matcherMatches, $argumentMatches)) {
            $this->assertEquals(
                new MatcherResult(false, $matcherMatches, $argumentMatches),
                $this->subject->explain($positionalMatchers, $parameterNames, $arguments),
                "Positional matchers with mixed arguments non-matching #$i"
            );
        }

        // positional matchers with named arguments
        foreach ($matchingNamedArguments as $i => list($arguments, $matcherMatches, $argumentMatches)) {
            $this->assertEquals(
                new MatcherResult(true, $matcherMatches, $argumentMatches),
                $this->subject->explain($positionalMatchers, $parameterNames, $arguments),
                "Positional matchers with named arguments matching #$i"
            );
        }
        foreach ($nonMatchingNamedArguments as $i => list($arguments, $matcherMatches, $argumentMatches)) {
            $this->assertEquals(
                new MatcherResult(false, $matcherMatches, $argumentMatches),
                $this->subject->explain($positionalMatchers, $parameterNames, $arguments),
                "Positional matchers with named arguments non-matching #$i"
            );
        }

        // mixed matchers with positional arguments
        foreach ($matchingPositionalArguments as $i => list($arguments, $matcherMatches, $argumentMatches)) {
            $matcherMatches = array_combine(array_keys($mixedMatchers), $matcherMatches);
            $this->assertEquals(
                new MatcherResult(true, $matcherMatches, $argumentMatches),
                $this->subject->explain($mixedMatchers, $parameterNames, $arguments),
                "Mixed matchers with positional arguments matching #$i"
            );
        }
        foreach ($nonMatchingPositionalArguments as $i => list($arguments, $matcherMatches, $argumentMatches)) {
            $matcherMatches = array_combine(array_keys($mixedMatchers), $matcherMatches);
            $this->assertEquals(
                new MatcherResult(false, $matcherMatches, $argumentMatches),
                $this->subject->explain($mixedMatchers, $parameterNames, $arguments),
                "Mixed matchers with positional arguments non-matching #$i"
            );
        }

        // mixed matchers with mixed arguments
        foreach ($matchingMixedArguments as $i => list($arguments, $matcherMatches, $argumentMatches)) {
            $matcherMatches = array_combine(array_keys($mixedMatchers), $matcherMatches);
            $this->assertEquals(
                new MatcherResult(true, $matcherMatches, $argumentMatches),
                $this->subject->explain($mixedMatchers, $parameterNames, $arguments),
                "Mixed matchers with mixed arguments matching #$i"
            );
        }
        foreach ($nonMatchingMixedArguments as $i => list($arguments, $matcherMatches, $argumentMatches)) {
            $matcherMatches = array_combine(array_keys($mixedMatchers), $matcherMatches);
            $this->assertEquals(
                new MatcherResult(false, $matcherMatches, $argumentMatches),
                $this->subject->explain($mixedMatchers, $parameterNames, $arguments),
                "Mixed matchers with mixed arguments non-matching #$i"
            );
        }

        // mixed matchers with named arguments
        foreach ($matchingNamedArguments as $i => list($arguments, $matcherMatches, $argumentMatches)) {
            $matcherMatches = array_combine(array_keys($mixedMatchers), $matcherMatches);
            $this->assertEquals(
                new MatcherResult(true, $matcherMatches, $argumentMatches),
                $this->subject->explain($mixedMatchers, $parameterNames, $arguments),
                "Mixed matchers with named arguments matching #$i"
            );
        }
        foreach ($nonMatchingNamedArguments as $i => list($arguments, $matcherMatches, $argumentMatches)) {
            $matcherMatches = array_combine(array_keys($mixedMatchers), $matcherMatches);
            $this->assertEquals(
                new MatcherResult(false, $matcherMatches, $argumentMatches),
                $this->subject->explain($mixedMatchers, $parameterNames, $arguments),
                "Mixed matchers with named arguments non-matching #$i"
            );
        }

        // named matchers with positional arguments
        foreach ($matchingPositionalArguments as $i => list($arguments, $matcherMatches, $argumentMatches)) {
            $matcherMatches = array_combine(array_keys($namedMatchers), $matcherMatches);
            $this->assertEquals(
                new MatcherResult(true, $matcherMatches, $argumentMatches),
                $this->subject->explain($namedMatchers, $parameterNames, $arguments),
                "Named matchers with positional arguments matching #$i"
            );
        }
        foreach ($nonMatchingPositionalArguments as $i => list($arguments, $matcherMatches, $argumentMatches)) {
            $matcherMatches = array_combine(array_keys($namedMatchers), $matcherMatches);
            $this->assertEquals(
                new MatcherResult(false, $matcherMatches, $argumentMatches),
                $this->subject->explain($namedMatchers, $parameterNames, $arguments),
                "Named matchers with positional arguments non-matching #$i"
            );
        }

        // named matchers with mixed arguments
        foreach ($matchingMixedArguments as $i => list($arguments, $matcherMatches, $argumentMatches)) {
            $matcherMatches = array_combine(array_keys($namedMatchers), $matcherMatches);
            $this->assertEquals(
                new MatcherResult(true, $matcherMatches, $argumentMatches),
                $this->subject->explain($namedMatchers, $parameterNames, $arguments),
                "Named matchers with mixed arguments matching #$i"
            );
        }
        foreach ($nonMatchingMixedArguments as $i => list($arguments, $matcherMatches, $argumentMatches)) {
            $matcherMatches = array_combine(array_keys($namedMatchers), $matcherMatches);
            $this->assertEquals(
                new MatcherResult(false, $matcherMatches, $argumentMatches),
                $this->subject->explain($namedMatchers, $parameterNames, $arguments),
                "Named matchers with mixed arguments non-matching #$i"
            );
        }

        // named matchers with named arguments
        foreach ($matchingNamedArguments as $i => list($arguments, $matcherMatches, $argumentMatches)) {
            $matcherMatches = array_combine(array_keys($namedMatchers), $matcherMatches);
            $this->assertEquals(
                new MatcherResult(true, $matcherMatches, $argumentMatches),
                $this->subject->explain($namedMatchers, $parameterNames, $arguments),
                "Named matchers with named arguments matching #$i"
            );
        }
        foreach ($nonMatchingNamedArguments as $i => list($arguments, $matcherMatches, $argumentMatches)) {
            $matcherMatches = array_combine(array_keys($namedMatchers), $matcherMatches);
            $this->assertEquals(
                new MatcherResult(false, $matcherMatches, $argumentMatches),
                $this->subject->explain($namedMatchers, $parameterNames, $arguments),
                "Named matchers with named arguments non-matching #$i"
            );
        }
    }

    public function testExplainWithWildcardMaximumArguments()
    {
        $parameterNames = ['a', 'b'];
        $matcher1 = $this->matcherFactory->equalTo(1);
        $matcher2 = $this->matcherFactory->equalTo(2);
        $wildcard = new WildcardMatcher($this->matcherFactory->equalTo(3), 0, 1);
        $positionalMatchers = [$matcher1, $matcher2, $wildcard];
        $mixedMatchers = [$matcher1, 'b' => $matcher2, $wildcard];
        $namedMatchers = ['a' => $matcher1, 'b' => $matcher2, 'x' => $wildcard];

        $matchingPositionalArguments = [
            [
                [1, 2],
                [true, true, true],
                [true, true],
            ],
            [
                [1, 2, 3],
                [true, true, true],
                [true, true, true],
            ],
        ];
        $nonMatchingPositionalArguments = [
            [
                [1, 2, 4],
                [true, true, true],
                [true, true, false],
            ],
            [
                [1, 2, 3, 3],
                [true, true, true],
                [true, true, true, false],
            ],
            [
                [1, 2, 3, 4],
                [true, true, true],
                [true, true, true, false],
            ],
            [
                [1, 2, 4, 3],
                [true, true, true],
                [true, true, false, false],
            ],
            [
                [1, 2, 4, 4],
                [true, true, true],
                [true, true, false, false],
            ],
        ];

        $matchingMixedArguments = [
            [
                [1, 'b' => 2],
                [true, true, true],
                [true, 'b' => true],
            ],
            [
                [1, 'b' => 2, 'c' => 3],
                [true, true, true],
                [true, 'b' => true, 'c' => true],
            ],
        ];
        $nonMatchingMixedArguments = [
            [
                [1, 'b' => 2, 'c' => 4],
                [true, true, true],
                [true, 'b' => true, 'c' => false],
            ],
            [
                [1, 'b' => 2, 'c' => 3, 'd' => 3],
                [true, true, true],
                [true, 'b' => true, 'c' => true, 'd' => false],
            ],
            [
                [1, 'b' => 2, 'c' => 3, 'd' => 4],
                [true, true, true],
                [true, 'b' => true, 'c' => true, 'd' => false],
            ],
            [
                [1, 'b' => 2, 'c' => 4, 'd' => 3],
                [true, true, true],
                [true, 'b' => true, 'c' => false, 'd' => false],
            ],
            [
                [1, 'b' => 2, 'c' => 4, 'd' => 4],
                [true, true, true],
                [true, 'b' => true, 'c' => false, 'd' => false],
            ],
        ];

        $matchingNamedArguments = [
            [
                ['a' => 1, 'b' => 2],
                [true, true, true],
                ['a' => true, 'b' => true],
            ],
            [
                ['a' => 1, 'b' => 2, 'c' => 3],
                [true, true, true],
                ['a' => true, 'b' => true, 'c' => true],
            ],
        ];
        $nonMatchingNamedArguments = [
            [
                ['a' => 1, 'b' => 2, 'c' => 4],
                [true, true, true],
                ['a' => true, 'b' => true, 'c' => false],
            ],
            [
                ['a' => 1, 'b' => 2, 'c' => 3, 'd' => 3],
                [true, true, true],
                ['a' => true, 'b' => true, 'c' => true, 'd' => false],
            ],
            [
                ['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4],
                [true, true, true],
                ['a' => true, 'b' => true, 'c' => true, 'd' => false],
            ],
            [
                ['a' => 1, 'b' => 2, 'c' => 4, 'd' => 3],
                [true, true, true],
                ['a' => true, 'b' => true, 'c' => false, 'd' => false],
            ],
            [
                ['a' => 1, 'b' => 2, 'c' => 4, 'd' => 4],
                [true, true, true],
                ['a' => true, 'b' => true, 'c' => false, 'd' => false],
            ],
        ];

        // positional matchers with positional arguments
        foreach ($matchingPositionalArguments as $i => list($arguments, $matcherMatches, $argumentMatches)) {
            $this->assertEquals(
                new MatcherResult(true, $matcherMatches, $argumentMatches),
                $this->subject->explain($positionalMatchers, $parameterNames, $arguments),
                "Positional matchers with positional arguments matching #$i"
            );
        }
        foreach ($nonMatchingPositionalArguments as $i => list($arguments, $matcherMatches, $argumentMatches)) {
            $this->assertEquals(
                new MatcherResult(false, $matcherMatches, $argumentMatches),
                $this->subject->explain($positionalMatchers, $parameterNames, $arguments),
                "Positional matchers with positional arguments non-matching #$i"
            );
        }

        // positional matchers with mixed arguments
        foreach ($matchingMixedArguments as $i => list($arguments, $matcherMatches, $argumentMatches)) {
            $this->assertEquals(
                new MatcherResult(true, $matcherMatches, $argumentMatches),
                $this->subject->explain($positionalMatchers, $parameterNames, $arguments),
                "Positional matchers with mixed arguments matching #$i"
            );
        }
        foreach ($nonMatchingMixedArguments as $i => list($arguments, $matcherMatches, $argumentMatches)) {
            $this->assertEquals(
                new MatcherResult(false, $matcherMatches, $argumentMatches),
                $this->subject->explain($positionalMatchers, $parameterNames, $arguments),
                "Positional matchers with mixed arguments non-matching #$i"
            );
        }

        // positional matchers with named arguments
        foreach ($matchingNamedArguments as $i => list($arguments, $matcherMatches, $argumentMatches)) {
            $this->assertEquals(
                new MatcherResult(true, $matcherMatches, $argumentMatches),
                $this->subject->explain($positionalMatchers, $parameterNames, $arguments),
                "Positional matchers with named arguments matching #$i"
            );
        }
        foreach ($nonMatchingNamedArguments as $i => list($arguments, $matcherMatches, $argumentMatches)) {
            $this->assertEquals(
                new MatcherResult(false, $matcherMatches, $argumentMatches),
                $this->subject->explain($positionalMatchers, $parameterNames, $arguments),
                "Positional matchers with named arguments non-matching #$i"
            );
        }

        // mixed matchers with positional arguments
        foreach ($matchingPositionalArguments as $i => list($arguments, $matcherMatches, $argumentMatches)) {
            $matcherMatches = array_combine(array_keys($mixedMatchers), $matcherMatches);
            $this->assertEquals(
                new MatcherResult(true, $matcherMatches, $argumentMatches),
                $this->subject->explain($mixedMatchers, $parameterNames, $arguments),
                "Mixed matchers with positional arguments matching #$i"
            );
        }
        foreach ($nonMatchingPositionalArguments as $i => list($arguments, $matcherMatches, $argumentMatches)) {
            $matcherMatches = array_combine(array_keys($mixedMatchers), $matcherMatches);
            $this->assertEquals(
                new MatcherResult(false, $matcherMatches, $argumentMatches),
                $this->subject->explain($mixedMatchers, $parameterNames, $arguments),
                "Mixed matchers with positional arguments non-matching #$i"
            );
        }

        // mixed matchers with mixed arguments
        foreach ($matchingMixedArguments as $i => list($arguments, $matcherMatches, $argumentMatches)) {
            $matcherMatches = array_combine(array_keys($mixedMatchers), $matcherMatches);
            $this->assertEquals(
                new MatcherResult(true, $matcherMatches, $argumentMatches),
                $this->subject->explain($mixedMatchers, $parameterNames, $arguments),
                "Mixed matchers with mixed arguments matching #$i"
            );
        }
        foreach ($nonMatchingMixedArguments as $i => list($arguments, $matcherMatches, $argumentMatches)) {
            $matcherMatches = array_combine(array_keys($mixedMatchers), $matcherMatches);
            $this->assertEquals(
                new MatcherResult(false, $matcherMatches, $argumentMatches),
                $this->subject->explain($mixedMatchers, $parameterNames, $arguments),
                "Mixed matchers with mixed arguments non-matching #$i"
            );
        }

        // mixed matchers with named arguments
        foreach ($matchingNamedArguments as $i => list($arguments, $matcherMatches, $argumentMatches)) {
            $matcherMatches = array_combine(array_keys($mixedMatchers), $matcherMatches);
            $this->assertEquals(
                new MatcherResult(true, $matcherMatches, $argumentMatches),
                $this->subject->explain($mixedMatchers, $parameterNames, $arguments),
                "Mixed matchers with named arguments matching #$i"
            );
        }
        foreach ($nonMatchingNamedArguments as $i => list($arguments, $matcherMatches, $argumentMatches)) {
            $matcherMatches = array_combine(array_keys($mixedMatchers), $matcherMatches);
            $this->assertEquals(
                new MatcherResult(false, $matcherMatches, $argumentMatches),
                $this->subject->explain($mixedMatchers, $parameterNames, $arguments),
                "Mixed matchers with named arguments non-matching #$i"
            );
        }

        // named matchers with positional arguments
        foreach ($matchingPositionalArguments as $i => list($arguments, $matcherMatches, $argumentMatches)) {
            $matcherMatches = array_combine(array_keys($namedMatchers), $matcherMatches);
            $this->assertEquals(
                new MatcherResult(true, $matcherMatches, $argumentMatches),
                $this->subject->explain($namedMatchers, $parameterNames, $arguments),
                "Named matchers with positional arguments matching #$i"
            );
        }
        foreach ($nonMatchingPositionalArguments as $i => list($arguments, $matcherMatches, $argumentMatches)) {
            $matcherMatches = array_combine(array_keys($namedMatchers), $matcherMatches);
            $this->assertEquals(
                new MatcherResult(false, $matcherMatches, $argumentMatches),
                $this->subject->explain($namedMatchers, $parameterNames, $arguments),
                "Named matchers with positional arguments non-matching #$i"
            );
        }

        // named matchers with mixed arguments
        foreach ($matchingMixedArguments as $i => list($arguments, $matcherMatches, $argumentMatches)) {
            $matcherMatches = array_combine(array_keys($namedMatchers), $matcherMatches);
            $this->assertEquals(
                new MatcherResult(true, $matcherMatches, $argumentMatches),
                $this->subject->explain($namedMatchers, $parameterNames, $arguments),
                "Named matchers with mixed arguments matching #$i"
            );
        }
        foreach ($nonMatchingMixedArguments as $i => list($arguments, $matcherMatches, $argumentMatches)) {
            $matcherMatches = array_combine(array_keys($namedMatchers), $matcherMatches);
            $this->assertEquals(
                new MatcherResult(false, $matcherMatches, $argumentMatches),
                $this->subject->explain($namedMatchers, $parameterNames, $arguments),
                "Named matchers with mixed arguments non-matching #$i"
            );
        }

        // named matchers with named arguments
        foreach ($matchingNamedArguments as $i => list($arguments, $matcherMatches, $argumentMatches)) {
            $matcherMatches = array_combine(array_keys($namedMatchers), $matcherMatches);
            $this->assertEquals(
                new MatcherResult(true, $matcherMatches, $argumentMatches),
                $this->subject->explain($namedMatchers, $parameterNames, $arguments),
                "Named matchers with named arguments matching #$i"
            );
        }
        foreach ($nonMatchingNamedArguments as $i => list($arguments, $matcherMatches, $argumentMatches)) {
            $matcherMatches = array_combine(array_keys($namedMatchers), $matcherMatches);
            $this->assertEquals(
                new MatcherResult(false, $matcherMatches, $argumentMatches),
                $this->subject->explain($namedMatchers, $parameterNames, $arguments),
                "Named matchers with named arguments non-matching #$i"
            );
        }
    }

    public function testExplainFailureWithWildcardBeforeValue()
    {
        $matchers = [
            new WildcardMatcher($this->matcherFactory->equalTo(2), 0, -1),
            $this->matcherFactory->equalTo(1),
        ];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Wildcard matchers cannot be followed by other matchers.');
        $this->subject->explain($matchers, [], [1]);
    }
}
