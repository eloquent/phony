<?php

declare(strict_types=1);

namespace Eloquent\Phony\Matcher\Verification;

use Eloquent\Phony\Matcher\MatcherFactory;
use Eloquent\Phony\Matcher\MatcherSet;
use Eloquent\Phony\Test\Facade\FacadeContainer;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class MatcherVerifierTest extends TestCase
{
    private static MatcherFactory $matcherFactory;
    private static MatcherVerifier $subject;

    public static function setUpBeforeClass(): void
    {
        $container = new FacadeContainer();
        self::$matcherFactory = $container->matcherFactory;
        self::$subject = $container->matcherVerifier;
    }

    public function invalidInputData(): array
    {
        return [
            'positional after named argument' => [
                'Cannot use a positional argument after a named argument.',
                [],
                ['a' => 1, 2],
            ],
            'named argument overwrites previous' => [
                'Named argument $a overwrites previous argument.',
                ['a'],
                [1, 'a' => 2],
            ],
        ];
    }

    /**
     * @dataProvider invalidInputData
     */
    public function testExplainFailureWithInvalidInput(string $expected, array $parameterNames, array $arguments)
    {
        $matcherSet = self::$matcherFactory->adaptSet($parameterNames, []);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($expected);

        self::$subject->explain($matcherSet, $arguments);
    }

    /**
     * @dataProvider invalidInputData
     */
    public function testMatchesFailureWithInvalidInput(string $expected, array $parameterNames, array $arguments)
    {
        $matcherSet = self::$matcherFactory->adaptSet($parameterNames, []);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($expected);

        self::$subject->matches($matcherSet, $arguments);
    }

    public function fixtureData(): array
    {
        $container = new FacadeContainer();
        $matcherFactory = $container->matcherFactory;

        $basePath = dirname(dirname(dirname(dirname(__DIR__))));
        $toScan = [$basePath . '/test/fixture/matcher-verifier'];
        $data = [];

        while (count($toScan) > 0) {
            $fixturesPath = array_pop($toScan);

            foreach (scandir($fixturesPath) as $fixture) {
                if ('.' === $fixture[0]) {
                    continue;
                }

                $fixturePath = $fixturesPath . '/' . $fixture;
                $fixturePathRelative = substr($fixturePath, strlen($basePath) + 1);

                if (!is_dir($fixturePath)) {
                    continue;
                }

                $toScan[] = $fixturePath;

                $expectedPath = "$fixturePath/expected";
                $verificationPath = "$fixturePath/verification.php";

                if (!is_file($expectedPath)) {
                    continue;
                }

                $expected = file_get_contents($expectedPath);
                require $verificationPath;

                $label = substr($fixturePathRelative, 30);
                $data[$label] = [
                    $fixturePathRelative,
                    $expected,
                    $parameterNames,
                    $matcherSets,
                    $matchingCases,
                    $nonMatchingCases,
                ];
            }
        }

        return $data;
    }

    /**
     * @dataProvider fixtureData
     * @testdox $path/verification.php
     */
    public function testFixtures(
        string $path,
        string $expected,
        array $parameterNames,
        array $matcherSets,
        array $matchingCases,
        array $nonMatchingCases
    ) {
        $cases = self::mergeCases($matchingCases, $nonMatchingCases);

        foreach ($matcherSets as $matcherSetLabel => $values) {
            $matcherSet = self::$matcherFactory->adaptSet($parameterNames, $values);
            $resultSets = $this->explainCases($matcherSet, $cases);

            $this->assertSame(
                "\n" . $expected,
                "\n" . self::renderResults($matcherSet, $resultSets),
                "explain() rendered results - $matcherSetLabel"
            );

            foreach ($resultSets as $caseLabel => list($isMatch, $arguments, $resultSet)) {
                $this->assertSame(
                    $isMatch,
                    $resultSet->isMatch,
                    "explain() overall result - $matcherSetLabel - $caseLabel"
                );

                $this->assertSame(
                    $isMatch,
                    self::$subject->matches($matcherSet, $arguments),
                    "matches() overall result - $matcherSetLabel - $caseLabel"
                );
            }
        }
    }

    /**
     * Call explain() for every case and return an array with the results.
     */
    public static function explainCases(MatcherSet $matcherSet, array $cases): array
    {
        $resultSets = [];

        foreach ($cases as $label => list($isMatch, $arguments)) {
            $resultSets[$label] =
                [$isMatch, $arguments, self::$subject->explain($matcherSet, $arguments)];
        }

        return $resultSets;
    }

    /**
     * Merge matching and non-matching cases.
     */
    public static function mergeCases(array $matchingCases, array $nonMatchingCases): array
    {
        $cases = [];

        foreach ($matchingCases as $label => $arguments) {
            $cases["matching - $label"] = [true, $arguments];
        }
        foreach ($nonMatchingCases as $label => $arguments) {
            $cases["non-matching - $label"] = [false, $arguments];
        }

        return $cases;
    }

    /**
     * Render a set of result sets similar to AssertionRenderer->renderCalledWith().
     */
    public static function renderResults(MatcherSet $matcherSet, array $resultSets): string
    {
        $declaredMatcherCounts = [];
        $variadicMatcherCounts = [];
        $wildcardResultCount = 0;
        $resultLines = [];

        foreach ($resultSets as $label => list(, , $resultSet)) {
            $resultLines[] = '';
            $resultLines[] = "# $label";
            $resultLines[] = 'isMatch = ' . ($resultSet->isMatch ? 'true' : 'false');
            $lineNumber = 0;

            foreach ($resultSet->declaredResults as $position => $result) {
                $matcherKey = $result->matcherKey;
                $argumentKey = $result->argumentKey;
                $isMatch = $result->isMatch;
                $isWildMatch = $result->isWildMatch;

                $declaredMatcherCounts[$position] = $declaredMatcherCounts[$position] ?? 0;

                if ($isMatch) {
                    ++$declaredMatcherCounts[$position];
                }

                $lineNumberFormatted = str_pad((string) $lineNumber, 2, '0', STR_PAD_LEFT);
                $name = $matcherSet->parameterNames[$position];

                if ($isWildMatch) {
                    $resultType = 'wild-match';
                    $extra = '';
                    $omitted = '';
                } else {
                    $resultType = $isMatch ? 'match' : 'non-match';
                    $extra = $matcherKey === null && $argumentKey !== null ? ' (extra)' : '';
                    $omitted = $argumentKey === null ? ' (omitted)' : '';
                }

                $resultLines[] = "$lineNumberFormatted declared $name: $resultType$extra$omitted";
                ++$lineNumber;
            }

            foreach ($resultSet->variadicResults as $result) {
                $matcherKey = $result->matcherKey;
                $argumentKey = $result->argumentKey;
                $isMatch = $result->isMatch;
                $isWildMatch = $result->isWildMatch;

                if (null !== $matcherKey) {
                    $variadicMatcherCounts[$matcherKey] = $variadicMatcherCounts[$matcherKey] ?? 0;

                    if ($isMatch) {
                        ++$variadicMatcherCounts[$matcherKey];
                    }
                }

                $lineNumberFormatted = str_pad((string) $lineNumber, 2, '0', STR_PAD_LEFT);
                $key = $argumentKey ?? $matcherKey;

                if ($isWildMatch) {
                    $resultType = 'wild-match';
                    $extra = '';
                    $omitted = '';
                } else {
                    $resultType = $isMatch ? 'match' : 'non-match';
                    $extra = $matcherKey === null && $argumentKey !== null ? ' (extra)' : '';
                    $omitted = $argumentKey === null ? ' (omitted)' : '';
                }

                $resultLines[] = "$lineNumberFormatted variadic $key: $resultType$extra$omitted";
                ++$lineNumber;
            }

            if ($resultSet->wildcardResult) {
                $missing = -$resultSet->wildcardResult->delta;
                $isMatch = $resultSet->wildcardResult->isMatch;

                if ($isMatch) {
                    ++$wildcardResultCount;
                } else {
                    $lineNumberFormatted = str_pad((string) $lineNumber, 2, '0', STR_PAD_LEFT);
                    $wildRange = "($matcherSet->wildcardMinimum-$matcherSet->wildcardMaximum)";
                    $resultLines[] = "$lineNumberFormatted wild $wildRange: non-match (missing $missing)";
                }
            }
        }

        uksort($variadicMatcherCounts, [__CLASS__, 'compareVariadicKeys']);

        $matcherLines = ['# matchers'];
        $lineNumber = 0;

        foreach ($declaredMatcherCounts as $position => $matcherCount) {
            $name = $matcherSet->parameterNames[$position];
            $omitted = $matcherSet->declaredMatchers[$position] ? '' : ' (omitted)';

            $lineNumberFormatted = str_pad((string) $lineNumber, 2, '0', STR_PAD_LEFT);
            $matcherLines[] = "$lineNumberFormatted declared $name: $matcherCount$omitted";
            ++$lineNumber;
        }

        foreach ($variadicMatcherCounts as $matcherKey => $matcherCount) {
            $lineNumberFormatted = str_pad((string) $lineNumber, 2, '0', STR_PAD_LEFT);
            $matcherLines[] = "$lineNumberFormatted variadic $matcherKey: $matcherCount";
            ++$lineNumber;
        }

        if ($matcherSet->wildcardMatcher) {
            $lineNumberFormatted = str_pad((string) $lineNumber, 2, '0', STR_PAD_LEFT);
            $wildRange = "($matcherSet->wildcardMinimum-$matcherSet->wildcardMaximum)";
            $matcherLines[] = "$lineNumberFormatted wild $wildRange: $wildcardResultCount";
        }

        return join("\n", [...$matcherLines, ...$resultLines]) . "\n";
    }

    private static function compareVariadicKeys(int|string $a, int|string $b): int
    {
        $aIsPositional = is_int($a);
        $bIsPositional = is_int($b);

        if ($aIsPositional && !$bIsPositional) {
            return -1;
        }
        if (!$aIsPositional && $bIsPositional) {
            return 1;
        }

        return $a < $b ? -1 : 1;
    }
}
