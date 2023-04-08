<?php

declare(strict_types=1);

namespace Eloquent\Phony\Assertion;

use Eloquent\Phony\Call\ArgumentNormalizer;
use Eloquent\Phony\Call\Arguments;
use Eloquent\Phony\Call\Call;
use Eloquent\Phony\Call\CallData;
use Eloquent\Phony\Call\Event\CalledEvent;
use Eloquent\Phony\Call\Event\CallEvent;
use Eloquent\Phony\Call\Event\ConsumedEvent;
use Eloquent\Phony\Call\Event\ProducedEvent;
use Eloquent\Phony\Call\Event\ReceivedEvent;
use Eloquent\Phony\Call\Event\ReceivedExceptionEvent;
use Eloquent\Phony\Call\Event\ReturnedEvent;
use Eloquent\Phony\Call\Event\ThrewEvent;
use Eloquent\Phony\Call\Event\UsedEvent;
use Eloquent\Phony\Difference\DifferenceEngine;
use Eloquent\Phony\Difference\DifferenceSequenceMatcher;
use Eloquent\Phony\Event\Event;
use Eloquent\Phony\Exporter\Exporter;
use Eloquent\Phony\Matcher\EqualToMatcher;
use Eloquent\Phony\Matcher\Matcher;
use Eloquent\Phony\Matcher\MatcherSet;
use Eloquent\Phony\Matcher\Verification\MatcherVerifier;
use Eloquent\Phony\Mock\Handle\Handle;
use Eloquent\Phony\Mock\Handle\InstanceHandle;
use Eloquent\Phony\Reflection\FeatureDetector;
use Eloquent\Phony\Spy\Spy;
use Eloquent\Phony\Verification\Cardinality;
use Generator;
use Throwable;
use Traversable;

/**
 * Renders various data for use in assertion messages.
 */
class AssertionRenderer
{
    /**
     * Construct a new call renderer.
     *
     * @param MatcherVerifier    $matcherVerifier    The matcher verifier to use.
     * @param Exporter           $exporter           The exporter to use.
     * @param DifferenceEngine   $differenceEngine   The difference engine to use.
     * @param FeatureDetector    $featureDetector    The feature detector to use.
     * @param ArgumentNormalizer $argumentNormalizer The argument normalizer to use.
     */
    public function __construct(
        MatcherVerifier $matcherVerifier,
        Exporter $exporter,
        DifferenceEngine $differenceEngine,
        FeatureDetector $featureDetector,
        ArgumentNormalizer $argumentNormalizer
    ) {
        $this->matcherVerifier = $matcherVerifier;
        $this->exporter = $exporter;
        $this->differenceEngine = $differenceEngine;
        $this->featureDetector = $featureDetector;
        $this->argumentNormalizer = $argumentNormalizer;

        $this->setUseColor(null);
    }

    /**
     * Turn on or off the use of ANSI colored output.
     *
     * Pass `null` to detect automatically.
     *
     * @param ?bool $useColor True to use color.
     */
    public function setUseColor(?bool $useColor): void
    {
        if (null === $useColor) {
            $useColor = $this->featureDetector->isSupported('stdout.ansi');
        }

        if ($useColor) {
            $this->reset = "\033[0m";
            $this->bold = "\033[1m";
            $this->faint = "\033[2m";
            $this->passStart = "\033[32m";
            $this->failStart = "\033[31m";
            $this->pass = $this->passStart . self::PASS . $this->reset;
            $this->fail = $this->failStart . self::FAIL . $this->reset;

            $this->addStart = "\033[33m\033[2m{+\033[0m\033[33m";
            $this->addEnd = "\033[2m+}\033[0m";
            $this->removeStart = "\033[36m\033[2m[-\033[0m\033[36m";
            $this->removeEnd = "\033[2m-]\033[0m";
        } else {
            $this->reset = '';
            $this->bold = '';
            $this->faint = '';
            $this->passStart = '';
            $this->failStart = '';
            $this->pass = self::PASS;
            $this->fail = self::FAIL;

            $this->addStart = '{+';
            $this->addEnd = '+}';
            $this->removeStart = '[-';
            $this->removeEnd = '-]';
        }
    }

    /**
     * Render a failed called() verification.
     *
     * @param Spy|Call    $subject     The subject.
     * @param Cardinality $cardinality The cardinality.
     *
     * @return string The rendered failure message.
     */
    public function renderCalled($subject, Cardinality $cardinality): string
    {
        $parameterNames = [];

        foreach ($subject->parameters() as $parameter) {
            if ($parameter->isVariadic()) {
                break;
            }

            $parameterNames[] = $parameter->getName();
        }

        $isCall = $subject instanceof Call;

        if ($isCall) {
            /** @var Call $subject */

            /** @var array<int,Call> $calls */
            $calls = [$subject];
            /** @var callable $callback */
            $callback = $subject->callback();
            $renderedSubject =
                $this->bold .
                $this->exporter->exportCallable($callback) .
                $this->reset;
        } else {
            /** @var Spy $subject */

            $calls = $subject->allCalls();
            $renderedSubject =
                $this->bold .
                $this->exporter->exportCallable($subject) .
                $this->reset;
        }

        $minimum = $cardinality->minimum();
        $maximum = $cardinality->maximum();
        $isNever = 0 === $maximum;

        if ($isNever) {
            $expected = 'Expected no ' . $renderedSubject . ' call.';
        } else {
            $expected =
                'Expected ' . $renderedSubject . ' call with any arguments.';
        }

        $totalCount = count($calls);
        $matchCount = $totalCount;

        if ($totalCount) {
            $namePadLength = 0;
            $normalizedArguments = [];

            foreach ($calls as $callIndex => $call) {
                $arguments = $this->argumentNormalizer->normalize(
                    $parameterNames,
                    $call->arguments()->all()
                );
                $normalizedArguments[$callIndex] = $arguments;

                foreach ($arguments as $positionOrName => $argument) {
                    $length = strlen((string) $positionOrName);

                    if ($length > $namePadLength) {
                        $namePadLength = $length;
                    }
                }
            }

            if ($isNever) {
                $renderedResult = $this->fail;
            } else {
                $renderedResult = $this->pass;
            }

            $renderedCalls = [];

            foreach ($calls as $callIndex => $call) {
                $arguments = $normalizedArguments[$callIndex];
                $renderedArguments = [];

                foreach ($arguments as $positionOrName => $argument) {
                    $paddedName = str_pad(
                        (string) $positionOrName,
                        $namePadLength,
                        ' ',
                        STR_PAD_LEFT
                    );
                    $renderedArguments[] =
                        '    ' .
                        $paddedName . ': ' .
                        $renderedResult .
                        ' ' . $this->exporter->export($argument);
                }

                if (empty($renderedArguments)) {
                    $renderedArgumentList = ' (no arguments)';
                } else {
                    $renderedArgumentList =
                        ':' . PHP_EOL . implode(PHP_EOL, $renderedArguments);
                }

                $renderedCalls[] =
                    $renderedResult .
                    ' Call #' .
                    $call->index() .
                    $renderedArgumentList;
            }

            $actual = PHP_EOL . implode(PHP_EOL, $renderedCalls);
        } else {
            $actual = '';
        }

        $cardinality = $this->renderCardinality(
            $minimum,
            $maximum,
            $matchCount,
            $totalCount,
            $totalCount,
            true
        );

        return $this->reset . $expected . $cardinality . $actual;
    }

    /**
     * Render a failed calledWith() verification.
     *
     * @param Spy|Call    $subject     The subject.
     * @param Cardinality $cardinality The cardinality.
     * @param MatcherSet  $matcherSet  The matcher set.
     *
     * @return string The rendered failure message.
     */
    public function renderCalledWith(
        Spy|Call $subject,
        Cardinality $cardinality,
        MatcherSet $matcherSet
    ): string {
        if ($matcherSet->isUnboundWildcardAny()) {
            return $this->renderCalled($subject, $cardinality);
        }

        if ($subject instanceof Call) {
            $calls = [$subject];
            /** @var callable $callback */
            $callback = $subject->callback();
            $renderedSubject =
                $this->bold .
                $this->exporter->exportCallable($callback) .
                $this->reset;
        } else {
            $calls = $subject->allCalls();
            $renderedSubject =
                $this->bold .
                $this->exporter->exportCallable($subject) .
                $this->reset;
        }

        $minimum = $cardinality->minimum();
        $maximum = $cardinality->maximum();
        $isNever = 0 === $maximum;

        $maxKeyLength = self::maxMatcherSetKeyLength($matcherSet);

        foreach ($calls as $call) {
            $keyLength = self::maxArgumentKeyLength($call->arguments());

            if ($keyLength > $maxKeyLength) {
                $maxKeyLength = $keyLength;
            }
        }

        $callArguments = [];
        $callResults = [];
        $totalCount = 0;
        $matchCount = 0;
        $declaredMatcherCounts = [];
        $variadicMatcherCounts = [];
        $wildcardMatcherCount = 0;

        foreach ($calls as $callIndex => $call) {
            ++$totalCount;

            $arguments = $call->arguments()->all();
            $callArguments[$callIndex] = $arguments;

            $callResult =
                $this->matcherVerifier->explain($matcherSet, $arguments);
            $callResults[$callIndex] = $callResult;

            if ($callResult->isMatch) {
                ++$matchCount;
            }

            foreach ($callResult->declaredResults as $position => $result) {
                $isMatch = $result->isMatch;
                $declaredMatcherCounts[$position] =
                    $declaredMatcherCounts[$position] ?? 0;

                if ($isMatch) {
                    ++$declaredMatcherCounts[$position];
                }
            }

            foreach ($callResult->variadicResults as $result) {
                $matcherKey = $result->matcherKey;
                $isMatch = $result->isMatch;

                if (null !== $matcherKey) {
                    $variadicMatcherCounts[$matcherKey] =
                        $variadicMatcherCounts[$matcherKey] ?? 0;

                    if ($isMatch) {
                        ++$variadicMatcherCounts[$matcherKey];
                    }
                }
            }

            if ($callResult->wildcardResult) {
                $isMatch = $callResult->wildcardResult->isMatch;

                if ($isMatch) {
                    ++$wildcardMatcherCount;
                }
            }
        }

        $matcherRows = [];

        foreach ($matcherSet->declaredMatchers as $position => $matcher) {
            $key = $matcherSet->parameterNames[$position];
            $matcherMatchCount = $declaredMatcherCounts[$position] ?? 0;

            $matcherRows[] = [$key, $matcherMatchCount, $matcher];
        }

        foreach ($matcherSet->variadicMatchers as $positionOrName => $matcher) {
            $key = (string) $positionOrName;
            $matcherMatchCount = $variadicMatcherCounts[$positionOrName] ?? 0;

            $matcherRows[] = [$key, $matcherMatchCount, $matcher];
        }

        if ($matcherSet->wildcardMatcher) {
            $matcherRows[] =
                ['...', $wildcardMatcherCount, $matcherSet->wildcardMatcher];
        }

        if (empty($matcherRows)) {
            $renderedCriteria = 'no arguments.';
        } else {
            $renderedCriteria = 'arguments:';

            foreach ($matcherRows as list($key, $matcherMatchCount, $matcher)) {
                $paddedKey =
                    str_pad($key, $maxKeyLength + 4, ' ', STR_PAD_LEFT);

                if (
                    $cardinality->matches($matcherMatchCount, $totalCount)
                ) {
                    $result = self::PASS;
                    $resultStart = $this->passStart;
                } else {
                    $result = self::FAIL;
                    $resultStart = $this->failStart;
                }

                $renderedResult = $resultStart . $result . $this->reset;

                if ($subject instanceof Call) {
                    $renderedMatchCount = '';
                } else {
                    $matchOrMatches = 1 ===
                        $matcherMatchCount ? 'match' : 'matches';
                    $renderedMatchCount =
                        " $resultStart" .
                        $this->faint .
                        "($matcherMatchCount $matchOrMatches)" .
                        $this->reset;
                }

                $renderedMatcher = $matcher
                    ? $matcher->describe($this->exporter)
                    : '<omitted>';

                $renderedCriteria .= PHP_EOL .
                    "$paddedKey: $renderedResult " .
                    "$renderedMatcher$renderedMatchCount";
            }
        }

        if ($subject instanceof Call) {
            if ($isNever) {
                $expected =
                    'Expected ' . $renderedSubject .
                    ' call #' . $subject->index() .
                    ' not to have ' . $renderedCriteria;
            } else {
                $expected =
                    'Expected ' . $renderedSubject .
                    ' call #' . $subject->index() .
                    ' to have ' . $renderedCriteria;
            }
        } else {
            if ($isNever) {
                $expected =
                    'Expected no ' . $renderedSubject .
                    ' call with ' . $renderedCriteria;
            } elseif ($cardinality->isAlways()) {
                $expected =
                    'Expected all ' . $renderedSubject .
                    ' calls to have ' . $renderedCriteria;
            } else {
                $expected =
                    'Expected ' . $renderedSubject .
                    ' call with ' . $renderedCriteria;
            }
        }

        if ($subject instanceof Call) {
            $cardinality = '';
        } else {
            $cardinality = $this->renderCardinality(
                $minimum,
                $maximum,
                $matchCount,
                $totalCount,
                $totalCount,
                false
            );
        }

        $actual = '';

        foreach ($calls as $callIndex => $call) {
            $actual .= PHP_EOL;

            $arguments = $callArguments[$callIndex];
            $callResult = $callResults[$callIndex];

            $actual .= ($callResult->isMatch xor $isNever)
                ? $this->pass
                : $this->fail;
            $actual .= ' Call #' . $call->index();

            if (
                $callResult->declaredResults ||
                $callResult->variadicResults ||
                $callResult->wildcardResult
            ) {
                $actual .= ':';
            } else {
                $actual .= ' (no arguments)';
            }

            $argumentRows = [];

            foreach ($callResult->declaredResults as $position => $result) {
                $isSingularMatch = $result->isMatch;
                $isWildMatch = $result->isWildMatch;

                $key = $matcherSet->parameterNames[$position];
                $isMatch = $isSingularMatch || $isWildMatch;

                if (array_key_exists($position, $arguments)) {
                    $hasArgument = true;
                    $argument = $arguments[$position];
                } elseif (array_key_exists($key, $arguments)) {
                    $hasArgument = true;
                    $argument = $arguments[$key];
                } else {
                    $hasArgument = false;
                    $argument = null;
                }

                $matcher = $matcherSet->declaredMatchers[$position] ?? null;

                $argumentRows[] =
                    [$key, $isMatch, $hasArgument, $argument, $matcher];
            }

            foreach ($callResult->variadicResults as $result) {
                $matcherKey = $result->matcherKey;
                $argumentKey = $result->argumentKey;
                $isSingularMatch = $result->isMatch;
                $isWildMatch = $result->isWildMatch;

                $key = (string) ($argumentKey ?? $matcherKey);
                $isMatch = $isSingularMatch || $isWildMatch;
                $hasArgument = null !== $argumentKey;
                $argument = $hasArgument ? $arguments[$argumentKey] : null;
                $matcher = null === $matcherKey
                    ? null
                    : $matcherSet->variadicMatchers[$matcherKey];

                $argumentRows[] =
                    [$key, $isMatch, $hasArgument, $argument, $matcher];
            }

            foreach ($argumentRows as $argumentRow) {
                list($key, $isMatch, $hasArgument, $argument, $matcher) =
                    $argumentRow;

                $paddedKey =
                    str_pad($key, $maxKeyLength + 4, ' ', STR_PAD_LEFT);
                $result = ($isMatch xor $isNever) ? $this->pass : $this->fail;

                if ($hasArgument) {
                    $value = $this->exporter->export($argument);

                    if (!$isMatch && $matcher instanceof EqualToMatcher) {
                        $value = $this->differenceEngine->difference(
                            $matcher->describe($this->exporter),
                            $value
                        );
                    }
                } else {
                    $value = '<omitted>';
                }

                $actual .= PHP_EOL . "$paddedKey: $result $value";
            }

            if (
                $callResult->wildcardResult &&
                !$callResult->wildcardResult->isMatch
            ) {
                $paddedKey = str_pad(
                    (string) '...',
                    $maxKeyLength + 4,
                    ' ',
                    STR_PAD_LEFT
                );
                $result = $isNever ? $this->pass : $this->fail;
                $missing = -$callResult->wildcardResult->delta;

                $actual .= PHP_EOL . "$paddedKey: $result <$missing missing>";
            }
        }

        return $this->reset . $expected . $cardinality . $actual;
    }

    /**
     * Render a failed responded() verification.
     *
     * @param Spy|Call    $subject     The subject.
     * @param Cardinality $cardinality The cardinality.
     *
     * @return string The rendered failure message.
     */
    public function renderResponded($subject, Cardinality $cardinality): string
    {
        $isCall = $subject instanceof Call;

        if ($isCall) {
            /** @var Call $subject */

            /** @var array<int,Call> $calls */
            $calls = [$subject];
            /** @var callable $callback */
            $callback = $subject->callback();
            $renderedCallee = $this->exporter->exportCallable($callback);
        } else {
            /** @var Spy $subject */
            $calls = $subject->allCalls();
            $renderedCallee = $this->exporter->exportCallable($subject);
        }

        $renderedSubject = $this->bold . $renderedCallee . $this->reset;

        $minimum = $cardinality->minimum();
        $maximum = $cardinality->maximum();
        $isNever = 0 === $maximum;

        if ($isCall) {
            /** @var Call $subject */

            if ($isNever) {
                $expected =
                    'Expected ' . $renderedSubject .
                    ' call #' . $subject->index() . ' not to respond.';
            } else {
                $expected =
                    'Expected ' . $renderedSubject .
                    ' call #' . $subject->index() . ' to respond.';
            }
        } else {
            /** @var Spy $subject */

            if ($isNever) {
                $expected = 'Expected ' . $renderedSubject . ' not to respond.';
            } elseif ($cardinality->isAlways()) {
                $expected =
                    'Expected all ' . $renderedSubject . ' calls to respond.';
            } else {
                $expected = 'Expected ' . $renderedSubject . ' to respond.';
            }
        }

        $totalCount = count($calls);
        $matchCount = 0;

        if ($totalCount) {
            $renderedCalls = [];

            foreach ($calls as $call) {
                $responseEvent = $call->responseEvent();

                if ($responseEvent) {
                    ++$matchCount;
                }

                if ($responseEvent xor $isNever) {
                    $renderedResult = $this->pass;
                } else {
                    $renderedResult = $this->fail;
                }

                $renderedArguments =
                    $this->renderCompactCallArguments($call, 0);

                if ($responseEvent instanceof ReturnedEvent) {
                    $returnValue = $responseEvent->value();
                    $renderedResponse =
                        'Returned ' . $this->exporter->export($returnValue);
                } elseif ($responseEvent instanceof ThrewEvent) {
                    $exception = $responseEvent->exception();
                    $renderedResponse =
                        'Threw ' . $this->exporter->export($exception);
                } else {
                    $renderedResponse = 'Never responded';
                }

                $renderedCalls[] =
                    $renderedResult .
                    ' Call #' . $call->index() .
                    ' - ' . $renderedCallee .
                    "($renderedArguments):" .
                    PHP_EOL . '    ' . $renderedResult .
                    ' ' . $renderedResponse;
            }

            $actual = PHP_EOL . implode(PHP_EOL, $renderedCalls);
        } else {
            $actual = '';
        }

        if ($isCall) {
            $cardinality = '';
        } else {
            $cardinality = $this->renderCardinality(
                $minimum,
                $maximum,
                $matchCount,
                $totalCount,
                $totalCount,
                false
            );
        }

        return $this->reset . $expected . $cardinality . $actual;
    }

    /**
     * Render a failed completed() verification.
     *
     * @param Spy|Call    $subject     The subject.
     * @param Cardinality $cardinality The cardinality.
     *
     * @return string The rendered failure message.
     */
    public function renderCompleted($subject, Cardinality $cardinality): string
    {
        $isCall = $subject instanceof Call;

        if ($isCall) {
            /** @var Call $subject */

            /** @var array<int,Call> $calls */
            $calls = [$subject];
            /** @var callable $callback */
            $callback = $subject->callback();
            $renderedCallee = $this->exporter->exportCallable($callback);
        } else {
            /** @var Spy $subject */

            $calls = $subject->allCalls();
            $renderedCallee = $this->exporter->exportCallable($subject);
        }

        $renderedSubject = $this->bold . $renderedCallee . $this->reset;

        $minimum = $cardinality->minimum();
        $maximum = $cardinality->maximum();
        $isNever = 0 === $maximum;

        if ($isCall) {
            /** @var Call $subject */

            if ($isNever) {
                $expected =
                    'Expected ' . $renderedSubject .
                    ' call #' . $subject->index() . ' not to complete.';
            } else {
                $expected =
                    'Expected ' . $renderedSubject .
                    ' call #' . $subject->index() . ' to complete.';
            }
        } else {
            /** @var Spy $subject */

            if ($isNever) {
                $expected =
                    'Expected ' . $renderedSubject . ' not to complete.';
            } elseif ($cardinality->isAlways()) {
                $expected =
                    'Expected all ' . $renderedSubject . ' calls to complete.';
            } else {
                $expected =
                    'Expected ' . $renderedSubject . ' to complete.';
            }
        }

        $totalCount = count($calls);
        $matchCount = 0;

        if ($totalCount) {
            $renderedCalls = [];

            foreach ($calls as $call) {
                $endEvent = $call->endEvent();

                if ($endEvent) {
                    ++$matchCount;
                }

                if ($endEvent xor $isNever) {
                    $renderedResult = $this->pass;
                } else {
                    $renderedResult = $this->fail;
                }

                $renderedArguments =
                    $this->renderCompactCallArguments($call, 0);
                $responseEvent = $call->responseEvent();

                if ($responseEvent instanceof ReturnedEvent) {
                    $returnValue = $responseEvent->value();

                    if (
                        is_array($returnValue) ||
                        $returnValue instanceof Traversable
                    ) {
                        $iterableEvents = $call->iterableEvents();
                        $renderedIterableEvents = [];

                        foreach ($iterableEvents as $event) {
                            if ($event instanceof UsedEvent) {
                                $renderedIterableEvents[] =
                                    '        - Started iterating';
                            } elseif ($event instanceof ProducedEvent) {
                                $iterableKey = $event->key();
                                $iterableValue = $event->value();

                                $renderedIterableEvents[] =
                                    '        - Produced ' .
                                    $this->exporter->export($iterableKey) .
                                    ' => ' .
                                    $this->exporter->export($iterableValue);
                            } elseif ($event instanceof ReceivedEvent) {
                                $iterableValue = $event->value();

                                $renderedIterableEvents[] =
                                    '        - Received ' .
                                    $this->exporter->export($iterableValue);
                            } elseif (
                                $event instanceof ReceivedExceptionEvent
                            ) {
                                $iterableException = $event->exception();

                                $renderedIterableEvents[] =
                                    '        - Received exception ' .
                                    $this->exporter
                                        ->export($iterableException);
                            }
                        }

                        if (empty($iterableEvents)) {
                            $renderedIterableEvents[] =
                                '        ' . $renderedResult .
                                ' Never started iterating';
                        } elseif ($endEvent instanceof ConsumedEvent) {
                            $renderedIterableEvents[] =
                                '        ' . $renderedResult .
                                ' Finished iterating';
                        } elseif ($endEvent instanceof ReturnedEvent) {
                            $eventValue = $endEvent->value();

                            $renderedIterableEvents[] =
                                '        ' . $renderedResult . ' Returned ' .
                                $this->exporter->export($eventValue);
                        } elseif ($endEvent instanceof ThrewEvent) {
                            $eventException = $endEvent->exception();

                            $renderedIterableEvents[] =
                                '        ' . $renderedResult . ' Threw ' .
                                $this->exporter->export($eventException);
                        } else {
                            $renderedIterableEvents[] =
                                '        ' . $renderedResult .
                                ' Never finished iterating';
                        }

                        $renderedResponse =
                            'Returned ' .
                            $this->exporter->export($returnValue, 0) .
                            ', then:' . PHP_EOL .
                            implode(PHP_EOL, $renderedIterableEvents);
                    } else {
                        $renderedResponse =
                            'Returned ' . $this->exporter->export($returnValue);
                    }
                } elseif ($responseEvent instanceof ThrewEvent) {
                    $exception = $responseEvent->exception();
                    $renderedResponse =
                        'Threw ' . $this->exporter->export($exception);
                } else {
                    $renderedResponse = 'Never responded';
                }

                $renderedCalls[] =
                    $renderedResult .
                    ' Call #' . $call->index() .
                    ' - ' . $renderedCallee .
                    "($renderedArguments):" .
                    PHP_EOL . '    ' . $renderedResult .
                    ' ' . $renderedResponse;
            }

            $actual = PHP_EOL . implode(PHP_EOL, $renderedCalls);
        } else {
            $actual = '';
        }

        if ($isCall) {
            $cardinality = '';
        } else {
            $cardinality = $this->renderCardinality(
                $minimum,
                $maximum,
                $matchCount,
                $totalCount,
                $totalCount,
                false
            );
        }

        return $this->reset . $expected . $cardinality . $actual;
    }

    /**
     * Render a failed responded() verification.
     *
     * @param Spy|Call    $subject     The subject.
     * @param Cardinality $cardinality The cardinality.
     * @param ?Matcher    $value       The value.
     *
     * @return string The rendered failure message.
     */
    public function renderReturned(
        $subject,
        Cardinality $cardinality,
        ?Matcher $value
    ): string {
        $isCall = $subject instanceof Call;

        if ($isCall) {
            /** @var Call $subject */

            /** @var array<int,Call> $calls */
            $calls = [$subject];
            /** @var callable $callback */
            $callback = $subject->callback();
            $renderedCallee = $this->exporter->exportCallable($callback);
        } else {
            /** @var Spy $subject */

            $calls = $subject->allCalls();
            $renderedCallee = $this->exporter->exportCallable($subject);
        }

        $renderedSubject = $this->bold . $renderedCallee . $this->reset;

        if ($value) {
            $renderedValue = $value->describe($this->exporter);
        } else {
            $renderedValue = '<any>';
        }

        $renderedCriteria =
            'behave like:' . PHP_EOL .
            '    ' . $this->fail . ' Returned ' . $renderedValue;

        $minimum = $cardinality->minimum();
        $maximum = $cardinality->maximum();
        $isNever = 0 === $maximum;

        if ($isCall) {
            /** @var Call $subject */

            if ($isNever) {
                $expected =
                    'Expected ' . $renderedSubject .
                    ' call #' . $subject->index() .
                    ' not to ' . $renderedCriteria;
            } else {
                $expected =
                    'Expected ' . $renderedSubject .
                    ' call #' . $subject->index() .
                    ' to ' . $renderedCriteria;
            }
        } else {
            /** @var Spy $subject */

            if ($isNever) {
                $expected =
                    'Expected ' . $renderedSubject .
                    ' not to ' . $renderedCriteria;
            } elseif ($cardinality->isAlways()) {
                $expected =
                    'Expected all ' . $renderedSubject .
                    ' calls to ' . $renderedCriteria;
            } else {
                $expected =
                    'Expected ' . $renderedSubject .
                    ' to ' . $renderedCriteria;
            }
        }

        $totalCount = count($calls);
        $matchCount = 0;

        if ($totalCount) {
            $renderedCalls = [];

            foreach ($calls as $call) {
                if ($responseEvent = $call->responseEvent()) {
                    list($exception, $returnValue) = $call->response();
                } else {
                    $returnValue = null;
                }

                if ($responseEvent instanceof ReturnedEvent) {
                    if ($value) {
                        $isMatch = $value->matches($returnValue);
                    } else {
                        $isMatch = true;
                    }
                } else {
                    $isMatch = false;
                }

                if ($isMatch) {
                    ++$matchCount;
                }

                if ($isMatch xor $isNever) {
                    $renderedResult = $this->pass;
                } else {
                    $renderedResult = $this->fail;
                }

                $renderedArguments =
                    $this->renderCompactCallArguments($call, 0);

                if ($responseEvent instanceof ReturnedEvent) {
                    $renderedReturnValue =
                        $this->exporter->export($returnValue);

                    if (!$isMatch && $value instanceof EqualToMatcher) {
                        $renderedReturnValue = $this->differenceEngine
                            ->difference($renderedValue, $renderedReturnValue);
                    }

                    $renderedResponse = 'Returned ' . $renderedReturnValue;
                } elseif ($responseEvent instanceof ThrewEvent) {
                    $renderedResponse =
                        'Threw ' . $this->exporter->export($exception);
                } else {
                    $renderedResponse = 'Never responded';
                }

                $renderedCalls[] =
                    $renderedResult . ' Call #' . $call->index() .
                    ' - ' . $renderedCallee .
                    "($renderedArguments):" .
                    PHP_EOL . '    ' . $renderedResult .
                    ' ' . $renderedResponse;
            }

            $actual = PHP_EOL . implode(PHP_EOL, $renderedCalls);
        } else {
            $actual = '';
        }

        if ($isCall) {
            $cardinality = '';
        } else {
            $cardinality = $this->renderCardinality(
                $minimum,
                $maximum,
                $matchCount,
                $totalCount,
                $totalCount,
                false
            );
        }

        return $this->reset . $expected . $cardinality . $actual;
    }

    /**
     * Render a failed threw() verification.
     *
     * @param Spy|Call            $subject     The subject.
     * @param Cardinality         $cardinality The cardinality.
     * @param Matcher|string|null $type        The type of exception.
     *
     * @return string The rendered failure message.
     */
    public function renderThrew(
        $subject,
        Cardinality $cardinality,
        $type
    ): string {
        $isCall = $subject instanceof Call;

        if ($isCall) {
            /** @var Call $subject */

            /** @var array<int,Call> $calls */
            $calls = [$subject];
            /** @var callable $callback */
            $callback = $subject->callback();
            $renderedCallee = $this->exporter->exportCallable($callback);
        } else {
            /** @var Spy $subject */

            $calls = $subject->allCalls();
            $renderedCallee = $this->exporter->exportCallable($subject);
        }

        $renderedSubject = $this->bold . $renderedCallee . $this->reset;

        if ($type instanceof Matcher) {
            $renderedType = $type->describe($this->exporter);
        } elseif (is_string($type)) {
            $renderedType = $type;
        } else {
            $renderedType = '<any>';
        }

        $renderedCriteria =
            'behave like:' . PHP_EOL .
            '    ' . $this->fail . ' Threw ' . $renderedType;

        $minimum = $cardinality->minimum();
        $maximum = $cardinality->maximum();
        $isNever = 0 === $maximum;

        if ($isCall) {
            /** @var Call $subject */

            if ($isNever) {
                $expected =
                    'Expected ' . $renderedSubject .
                    ' call #' . $subject->index() .
                    ' not to ' . $renderedCriteria;
            } else {
                $expected =
                    'Expected ' . $renderedSubject .
                    ' call #' . $subject->index() .
                    ' to ' . $renderedCriteria;
            }
        } else {
            /** @var Spy $subject */

            if ($isNever) {
                $expected =
                    'Expected ' . $renderedSubject .
                    ' not to ' . $renderedCriteria;
            } elseif ($cardinality->isAlways()) {
                $expected =
                    'Expected all ' . $renderedSubject .
                    ' calls to ' . $renderedCriteria;
            } else {
                $expected =
                    'Expected ' . $renderedSubject .
                    ' to ' . $renderedCriteria;
            }
        }

        $totalCount = count($calls);
        $matchCount = 0;

        if ($totalCount) {
            $renderedCalls = [];

            foreach ($calls as $call) {
                if ($responseEvent = $call->responseEvent()) {
                    list($exception, $returnValue) = $call->response();
                } else {
                    $exception = null;
                    $returnValue = null;
                }

                if ($responseEvent instanceof ThrewEvent) {
                    /** @var Throwable $exception */

                    if ($type instanceof Matcher) {
                        $isMatch = $type->matches($exception);
                    } elseif (is_string($type)) {
                        $isMatch = is_a($exception, $type);
                    } else {
                        $isMatch = true;
                    }
                } else {
                    $isMatch = false;
                }

                if ($isMatch) {
                    ++$matchCount;
                }

                if ($isMatch xor $isNever) {
                    $renderedResult = $this->pass;
                } else {
                    $renderedResult = $this->fail;
                }

                $renderedArguments =
                    $this->renderCompactCallArguments($call, 0);

                if ($responseEvent instanceof ReturnedEvent) {
                    $renderedResponse =
                        'Returned ' . $this->exporter->export($returnValue);
                } elseif ($responseEvent instanceof ThrewEvent) {
                    $renderedException = $this->exporter->export($exception);

                    if (!$isMatch && $type instanceof EqualToMatcher) {
                        $renderedException = $this->differenceEngine
                            ->difference($renderedType, $renderedException);
                    }

                    $renderedResponse = 'Threw ' . $renderedException;
                } else {
                    $renderedResponse = 'Never responded';
                }

                $renderedCalls[] =
                    $renderedResult . ' Call #' . $call->index() .
                    ' - ' . $renderedCallee .
                    "($renderedArguments):" .
                    PHP_EOL . '    ' . $renderedResult .
                    ' ' . $renderedResponse;
            }

            $actual = PHP_EOL . implode(PHP_EOL, $renderedCalls);
        } else {
            $actual = '';
        }

        if ($isCall) {
            $cardinality = '';
        } else {
            $cardinality = $this->renderCardinality(
                $minimum,
                $maximum,
                $matchCount,
                $totalCount,
                $totalCount,
                false
            );
        }

        return $this->reset . $expected . $cardinality . $actual;
    }

    /**
     * Render a failed generated() verification.
     *
     * @param Spy|Call    $subject     The subject.
     * @param Cardinality $cardinality The cardinality.
     *
     * @return string The rendered failure message.
     */
    public function renderGenerated($subject, Cardinality $cardinality): string
    {
        $isCall = $subject instanceof Call;

        if ($isCall) {
            /** @var Call $subject */

            /** @var array<int,Call> $calls */
            $calls = [$subject];
            /** @var callable $callback */
            $callback = $subject->callback();
            $renderedCallee = $this->exporter->exportCallable($callback);
        } else {
            /** @var Spy $subject */

            $calls = $subject->allCalls();
            $renderedCallee = $this->exporter->exportCallable($subject);
        }

        $renderedSubject = $this->bold . $renderedCallee . $this->reset;
        $renderedCriteria =
            'behave like:' . PHP_EOL .
            '    ' . $this->fail . ' Returned Generator';

        $minimum = $cardinality->minimum();
        $maximum = $cardinality->maximum();
        $isNever = 0 === $maximum;

        if ($isCall) {
            /** @var Call $subject */

            if ($isNever) {
                $expected =
                    'Expected ' . $renderedSubject .
                    ' call #' . $subject->index() .
                    ' not to ' . $renderedCriteria;
            } else {
                $expected =
                    'Expected ' . $renderedSubject .
                    ' call #' . $subject->index() .
                    ' to ' . $renderedCriteria;
            }
        } else {
            /** @var Spy $subject */

            if ($isNever) {
                $expected =
                    'Expected ' . $renderedSubject .
                    ' not to ' . $renderedCriteria;
            } elseif ($cardinality->isAlways()) {
                $expected =
                    'Expected all ' . $renderedSubject .
                    ' calls to ' . $renderedCriteria;
            } else {
                $expected =
                    'Expected ' . $renderedSubject .
                    ' to ' . $renderedCriteria;
            }
        }

        $totalCount = count($calls);
        $matchCount = 0;

        if ($totalCount) {
            $renderedCalls = [];

            foreach ($calls as $call) {
                if ($responseEvent = $call->responseEvent()) {
                    list($exception, $returnValue) = $call->response();
                } else {
                    $returnValue = null;
                }

                if ($responseEvent instanceof ReturnedEvent) {
                    $isMatch = $returnValue instanceof Generator;
                } else {
                    $isMatch = false;
                }

                if ($isMatch) {
                    ++$matchCount;
                }

                if ($isMatch xor $isNever) {
                    $renderedResult = $this->pass;
                } else {
                    $renderedResult = $this->fail;
                }

                $renderedArguments =
                    $this->renderCompactCallArguments($call, 0);

                if ($responseEvent instanceof ReturnedEvent) {
                    $renderedResponse =
                        'Returned ' . $this->exporter->export($returnValue);
                } elseif ($responseEvent instanceof ThrewEvent) {
                    $renderedResponse =
                        'Threw ' . $this->exporter->export($exception);
                } else {
                    $renderedResponse = 'Never responded';
                }

                $renderedCalls[] =
                    $renderedResult . ' Call #' . $call->index() .
                    ' - ' . $renderedCallee .
                    "($renderedArguments):" .
                    PHP_EOL . '    ' . $renderedResult .
                    ' ' . $renderedResponse;
            }

            $actual = PHP_EOL . implode(PHP_EOL, $renderedCalls);
        } else {
            $actual = '';
        }

        if ($isCall) {
            $cardinality = '';
        } else {
            $cardinality = $this->renderCardinality(
                $minimum,
                $maximum,
                $matchCount,
                $totalCount,
                $totalCount,
                false
            );
        }

        return $this->reset . $expected . $cardinality . $actual;
    }

    /**
     * Render a failed iterated() verification.
     *
     * @param Spy|Call    $subject     The subject.
     * @param Cardinality $cardinality The cardinality.
     *
     * @return string The rendered failure message.
     */
    public function renderIterated($subject, Cardinality $cardinality): string
    {
        $isCall = $subject instanceof Call;

        if ($isCall) {
            /** @var Call $subject */

            /** @var array<int,Call> $calls */
            $calls = [$subject];
            /** @var callable $callback */
            $callback = $subject->callback();
            $renderedCallee = $this->exporter->exportCallable($callback);
        } else {
            /** @var Spy $subject */

            $calls = $subject->allCalls();
            $renderedCallee = $this->exporter->exportCallable($subject);
        }

        $renderedSubject = $this->bold . $renderedCallee . $this->reset;
        $renderedCriteria =
            'behave like:' . PHP_EOL .
            '    ' . $this->fail . ' Returned <iterable>';

        $minimum = $cardinality->minimum();
        $maximum = $cardinality->maximum();
        $isNever = 0 === $maximum;

        if ($isCall) {
            /** @var Call $subject */

            if ($isNever) {
                $expected =
                    'Expected ' . $renderedSubject .
                    ' call #' . $subject->index() .
                    ' not to ' . $renderedCriteria;
            } else {
                $expected =
                    'Expected ' . $renderedSubject .
                    ' call #' . $subject->index() .
                    ' to ' . $renderedCriteria;
            }
        } else {
            /** @var Spy $subject */

            if ($isNever) {
                $expected =
                    'Expected ' . $renderedSubject .
                    ' not to ' . $renderedCriteria;
            } elseif ($cardinality->isAlways()) {
                $expected =
                    'Expected all ' . $renderedSubject .
                    ' calls to ' . $renderedCriteria;
            } else {
                $expected =
                    'Expected ' . $renderedSubject .
                    ' to ' . $renderedCriteria;
            }
        }

        $totalCount = count($calls);
        $matchCount = 0;

        if ($totalCount) {
            $renderedCalls = [];

            foreach ($calls as $call) {
                if ($responseEvent = $call->responseEvent()) {
                    list($exception, $returnValue) = $call->response();
                } else {
                    $returnValue = null;
                }

                if ($responseEvent instanceof ReturnedEvent) {
                    $isMatch = $returnValue instanceof Traversable ||
                        is_array($returnValue);
                } else {
                    $isMatch = false;
                }

                if ($isMatch) {
                    ++$matchCount;
                }

                if ($isMatch xor $isNever) {
                    $renderedResult = $this->pass;
                } else {
                    $renderedResult = $this->fail;
                }

                $renderedArguments =
                    $this->renderCompactCallArguments($call, 0);

                if ($responseEvent instanceof ReturnedEvent) {
                    $renderedResponse =
                        'Returned ' . $this->exporter->export($returnValue);
                } elseif ($responseEvent instanceof ThrewEvent) {
                    $renderedResponse =
                        'Threw ' . $this->exporter->export($exception);
                } else {
                    $renderedResponse = 'Never responded';
                }

                $renderedCalls[] =
                    $renderedResult . ' Call #' . $call->index() .
                    ' - ' . $renderedCallee .
                    "($renderedArguments):" .
                    PHP_EOL . '    ' . $renderedResult .
                    ' ' . $renderedResponse;
            }

            $actual = PHP_EOL . implode(PHP_EOL, $renderedCalls);
        } else {
            $actual = '';
        }

        if ($isCall) {
            $cardinality = '';
        } else {
            $cardinality = $this->renderCardinality(
                $minimum,
                $maximum,
                $matchCount,
                $totalCount,
                $totalCount,
                false
            );
        }

        return $this->reset . $expected . $cardinality . $actual;
    }

    /**
     * Render a failed iterable used() verification.
     *
     * @param Spy|Call    $subject     The subject.
     * @param Cardinality $cardinality The cardinality.
     * @param bool        $isGenerator True if this verification is for a generator.
     *
     * @return string The rendered failure message.
     */
    public function renderIterableUsed(
        $subject,
        Cardinality $cardinality,
        bool $isGenerator
    ): string {
        $isCall = $subject instanceof Call;

        if ($isCall) {
            /** @var Call $subject */

            /** @var array<int,Call> $calls */
            $calls = [$subject];
            /** @var callable $callback */
            $callback = $subject->callback();
            $renderedCallee = $this->exporter->exportCallable($callback);
        } else {
            /** @var Spy $subject */

            $calls = $subject->allCalls();
            $renderedCallee = $this->exporter->exportCallable($subject);
        }

        $renderedSubject = $this->bold . $renderedCallee . $this->reset;

        $minimum = $cardinality->minimum();
        $maximum = $cardinality->maximum();
        $isNever = 0 === $maximum;

        if ($isCall) {
            $totalCount = 1;
            $iterableCount = 0;

            if ($isNever) {
                $iterableResult = $this->fail;
            } else {
                $iterableResult = $this->pass;
            }

            $renderedIterableCount = '';
        } else {
            $totalCount = 0;
            $iterableCount = 0;

            foreach ($calls as $call) {
                ++$totalCount;

                if ($isGenerator) {
                    $isIterable = $call->isGenerator();
                } else {
                    $isIterable = $call->isIterable();
                }

                if ($isIterable) {
                    ++$iterableCount;
                }
            }

            if ($cardinality->matches($iterableCount, $iterableCount)) {
                $iterableResultStart = $this->passStart;
                $iterableResultText = self::PASS;
            } else {
                $iterableResultStart = $this->failStart;
                $iterableResultText = self::FAIL;
            }

            $iterableResult =
                $iterableResultStart .
                $iterableResultText .
                $this->reset;
            $matchOrMatches = 1 === $iterableCount ? 'match' : 'matches';
            $renderedIterableCount =
                ' ' . $iterableResultStart . $this->faint .
                '(' . $iterableCount . ' ' . $matchOrMatches . ')' .
                $this->reset;
        }

        if ($isGenerator) {
            $renderedIterableType = 'Generator';
        } else {
            $renderedIterableType = '<iterable>';
        }

        $renderedCriteria =
            'behave like:' . PHP_EOL .
            '    ' . $iterableResult .
            ' Returned ' . $renderedIterableType .
            ', then:' . $renderedIterableCount . PHP_EOL .
            '        ' . $this->fail . ' Started iterating';

        if ($isCall) {
            /** @var Call $subject */

            if ($isNever) {
                $expected =
                    'Expected ' . $renderedSubject .
                    ' call #' . $subject->index() .
                    ' not to ' . $renderedCriteria;
            } else {
                $expected =
                    'Expected ' . $renderedSubject .
                    ' call #' . $subject->index() .
                    ' to ' . $renderedCriteria;
            }
        } else {
            /** @var Spy $subject */

            if ($isGenerator) {
                $renderedIterableType = 'generator calls';
            } else {
                $renderedIterableType = 'iterable calls';
            }

            if ($isNever) {
                $expected =
                    'Expected ' . $renderedSubject .
                    ' ' . $renderedIterableType .
                    ' not to ' . $renderedCriteria;
            } elseif ($cardinality->isAlways()) {
                $expected =
                    'Expected all ' . $renderedSubject .
                    ' ' . $renderedIterableType .
                    ' to ' . $renderedCriteria;
            } else {
                $expected =
                    'Expected ' . $renderedSubject .
                    ' ' . $renderedIterableType .
                    ' to ' . $renderedCriteria;
            }
        }

        $renderedCalls = [];
        $matchCount = 0;

        foreach ($calls as $call) {
            if ($isGenerator) {
                $callIsRelevant = $call->isGenerator();
            } else {
                $callIsRelevant = $call->isIterable();
            }

            if ($callIsRelevant) {
                $callStart = '';
                $callEnd = '';
            } else {
                $callStart = $this->faint;
                $callEnd = $this->reset;
            }

            $isMatch = false;
            $renderedArguments = $this->renderCompactCallArguments($call, 0);

            $responseEvent = $call->responseEvent();

            if ($responseEvent instanceof ReturnedEvent) {
                $returnValue = $responseEvent->value();

                if (
                    is_array($returnValue) ||
                    $returnValue instanceof Traversable
                ) {
                    $iterableEvents = $call->iterableEvents();
                    $renderedIterableEvents = [];

                    foreach ($iterableEvents as $event) {
                        if ($event instanceof UsedEvent) {
                            if ($callIsRelevant) {
                                $isMatch = true;

                                if ($isNever) {
                                    $eventResult = $this->fail;
                                } else {
                                    $eventResult = $this->pass;
                                }
                            } else {
                                $eventResult = '-';
                            }

                            $renderedIterableEvents[] =
                                '        ' . $eventResult .
                                ' Started iterating';
                        } elseif ($event instanceof ProducedEvent) {
                            $iterableKey = $event->key();
                            $iterableValue = $event->value();

                            $renderedIterableEvents[] =
                                '        - Produced ' .
                                $this->exporter->export($iterableKey) .
                                ' => ' .
                                $this->exporter->export($iterableValue);
                        } elseif ($event instanceof ReceivedEvent) {
                            $iterableValue = $event->value();

                            $renderedIterableEvents[] =
                                '        - Received ' .
                                $this->exporter->export($iterableValue);
                        } elseif (
                            $event instanceof ReceivedExceptionEvent
                        ) {
                            $iterableException = $event->exception();

                            $renderedIterableEvents[] =
                                '        - Received exception ' .
                                $this->exporter->export($iterableException);
                        }
                    }

                    $endEvent = $call->endEvent();

                    if (empty($iterableEvents)) {
                        if ($callIsRelevant) {
                            if ($isNever) {
                                $eventResult = $this->pass;
                            } else {
                                $eventResult = $this->fail;
                            }
                        } else {
                            $eventResult = '-';
                        }

                        $renderedIterableEvents[] =
                            '        ' . $eventResult .
                            ' Never started iterating';
                    } elseif ($endEvent instanceof ConsumedEvent) {
                        $renderedIterableEvents[] =
                            '        - Finished iterating';
                    } elseif ($endEvent instanceof ReturnedEvent) {
                        $eventValue = $endEvent->value();

                        $renderedIterableEvents[] =
                            '        - Returned ' .
                            $this->exporter->export($eventValue);
                    } elseif ($endEvent instanceof ThrewEvent) {
                        $eventException = $endEvent->exception();

                        $renderedIterableEvents[] =
                            '        - Threw ' .
                            $this->exporter->export($eventException);
                    } else {
                        $renderedIterableEvents[] =
                            '        - Never finished iterating';
                    }

                    $renderedResponse =
                        'Returned ' . $this->exporter->export($returnValue, 0) .
                        ', then:' . $callEnd . PHP_EOL . $callStart .
                        implode(
                            $callEnd . PHP_EOL . $callStart,
                            $renderedIterableEvents
                        );
                } else {
                    $renderedResponse =
                        'Returned ' . $this->exporter->export($returnValue);
                }
            } elseif ($responseEvent instanceof ThrewEvent) {
                $exception = $responseEvent->exception();
                $renderedResponse =
                    'Threw ' . $this->exporter->export($exception);
            } else {
                $renderedResponse = 'Never responded';
            }

            if ($isMatch) {
                ++$matchCount;
            }

            if ($callIsRelevant) {
                if ($isMatch xor $isNever) {
                    $renderedResult = $this->pass;
                } else {
                    $renderedResult = $this->fail;
                }
            } else {
                $renderedResult = '-';
            }

            $renderedCalls[] =
                $callStart . $renderedResult . ' Call #' . $call->index() .
                ' - ' . $renderedCallee .
                "($renderedArguments):" .
                $callEnd . PHP_EOL .
                $callStart . '    ' . $renderedResult . ' ' .
                $renderedResponse . $callEnd;
        }

        $actual = PHP_EOL . implode(PHP_EOL, $renderedCalls);

        if ($isCall) {
            $cardinality = '';
        } else {
            $cardinality = $this->renderCardinality(
                $minimum,
                $maximum,
                $matchCount,
                $iterableCount,
                $totalCount,
                false
            );
        }

        return $this->reset . $expected . $cardinality . $actual;
    }

    /**
     * Render a failed iterable produced() verification.
     *
     * @param Spy|Call    $subject     The subject.
     * @param Cardinality $cardinality The cardinality.
     * @param bool        $isGenerator True if this verification is for a generator.
     * @param ?Matcher    $key         The key.
     * @param ?Matcher    $value       The value.
     *
     * @return string The rendered failure message.
     */
    public function renderIterableProduced(
        $subject,
        Cardinality $cardinality,
        bool $isGenerator,
        ?Matcher $key,
        ?Matcher $value
    ): string {
        $isCall = $subject instanceof Call;

        if ($isCall) {
            /** @var Call $subject */

            /** @var array<int,Call> $calls */
            $calls = [$subject];
            /** @var callable $callback */
            $callback = $subject->callback();
            $renderedCallee = $this->exporter->exportCallable($callback);
        } else {
            /** @var Spy $subject */

            $calls = $subject->allCalls();
            $renderedCallee = $this->exporter->exportCallable($subject);
        }

        $renderedSubject = $this->bold . $renderedCallee . $this->reset;

        $minimum = $cardinality->minimum();
        $maximum = $cardinality->maximum();
        $isNever = 0 === $maximum;

        if ($isCall) {
            /** @var Call $subject */

            $totalCount = 0;
            $callCount = 1;
            $iterableCount = 1;

            foreach ($subject->iterableEvents() as $event) {
                if ($event instanceof ProducedEvent) {
                    ++$totalCount;
                }
            }

            $renderedIterableCount = '';
        } else {
            /** @var Spy $subject */

            $callCount = 0;
            $iterableCount = 0;

            foreach ($calls as $call) {
                ++$callCount;

                if ($isGenerator) {
                    $isIterable = $call->isGenerator();
                } else {
                    $isIterable = $call->isIterable();
                }

                if ($isIterable) {
                    ++$iterableCount;
                }
            }

            $totalCount = $iterableCount;

            $matchOrMatches = 1 === $iterableCount ? 'match' : 'matches';
            $renderedIterableCount =
                ' ' . $this->faint .
                '(' . $iterableCount .
                ' ' . $matchOrMatches .
                ')' . $this->reset;
        }

        if ($iterableCount xor $isNever) {
            $iterableResult = $this->pass;
        } else {
            $iterableResult = $this->fail;
        }

        if ($isGenerator) {
            $renderedIterableType = 'Generator';
        } else {
            $renderedIterableType = '<iterable>';
        }

        if ($key) {
            $renderedKey = $key->describe($this->exporter);
        } else {
            $renderedKey = '<any>';
        }

        if ($value) {
            $renderedValue = $value->describe($this->exporter);
        } else {
            $renderedValue = '<any>';
        }

        $renderedCriteria =
            'behave like:' . PHP_EOL .
            '    ' . $iterableResult .
            ' Returned ' . $renderedIterableType .
            ', then:' . $renderedIterableCount . PHP_EOL .
            '        ' . $this->fail .
            ' Produced ' . $renderedKey .
            ' => ' . $renderedValue;

        if ($isCall) {
            /** @var Call $subject */

            if ($isNever) {
                $expected =
                    'Expected ' . $renderedSubject .
                    ' call #' . $subject->index() .
                    ' not to ' . $renderedCriteria;
            } else {
                $expected =
                    'Expected ' . $renderedSubject .
                    ' call #' . $subject->index() .
                    ' to ' . $renderedCriteria;
            }
        } else {
            /** @var Spy $subject */

            if ($isGenerator) {
                $renderedIterableType = 'generator calls';
            } else {
                $renderedIterableType = 'iterable calls';
            }

            if ($isNever) {
                $expected =
                    'Expected ' . $renderedSubject .
                    ' ' . $renderedIterableType .
                    ' not to ' . $renderedCriteria;
            } elseif ($cardinality->isAlways()) {
                $expected =
                    'Expected all ' . $renderedSubject .
                    ' ' . $renderedIterableType .
                    ' to ' . $renderedCriteria;
            } else {
                $expected =
                    'Expected ' . $renderedSubject .
                    ' ' . $renderedIterableType .
                    ' to ' . $renderedCriteria;
            }
        }

        $renderedCalls = [];
        $matchCount = 0;

        foreach ($calls as $call) {
            if ($isGenerator) {
                $callIsRelevant = $call->isGenerator();
            } else {
                $callIsRelevant = $call->isIterable();
            }

            if ($callIsRelevant) {
                $callStart = '';
                $callEnd = '';
            } else {
                $callStart = $this->faint;
                $callEnd = $this->reset;
            }

            $isMatchingCall = false;
            $renderedArguments = $this->renderCompactCallArguments($call, 0);

            $responseEvent = $call->responseEvent();

            if ($responseEvent instanceof ReturnedEvent) {
                $returnValue = $responseEvent->value();

                if (
                    is_array($returnValue) ||
                    $returnValue instanceof Traversable
                ) {
                    $iterableEvents = $call->iterableEvents();
                    $renderedIterableEvents = [];

                    foreach ($iterableEvents as $event) {
                        if ($event instanceof UsedEvent) {
                            $renderedIterableEvents[] =
                                '        - Started iterating';
                        } elseif ($event instanceof ProducedEvent) {
                            $iterableKey = $event->key();
                            $renderedIterableKey =
                                $this->exporter->export($iterableKey);

                            $iterableValue = $event->value();
                            $renderedIterableValue =
                                $this->exporter->export($iterableValue);

                            if ($callIsRelevant) {
                                $isKeyMatch =
                                    !$key || $key->matches($iterableKey);
                                $isValueMatch =
                                    !$value ||
                                    $value->matches($iterableValue);
                                $eventIsMatch = $isKeyMatch && $isValueMatch;

                                if ($eventIsMatch) {
                                    $isMatchingCall = true;

                                    if ($isCall) {
                                        ++$matchCount;
                                    }
                                }

                                if (
                                    !$isKeyMatch &&
                                    $key instanceof EqualToMatcher
                                ) {
                                    $renderedIterableKey =
                                        $this->differenceEngine->difference(
                                            $renderedKey,
                                            $renderedIterableKey
                                        );
                                }

                                if (
                                    !$isValueMatch &&
                                    $value instanceof EqualToMatcher
                                ) {
                                    $renderedIterableValue =
                                        $this->differenceEngine->difference(
                                            $renderedValue,
                                            $renderedIterableValue
                                        );
                                }

                                if ($eventIsMatch xor $isNever) {
                                    $eventResult = $this->pass;
                                } else {
                                    $eventResult = $this->fail;
                                }
                            } else {
                                $eventResult = '-';
                            }

                            $renderedIterableEvents[] =
                                '        ' . $eventResult .
                                ' Produced ' . $renderedIterableKey .
                                ' => ' . $renderedIterableValue;
                        } elseif ($event instanceof ReceivedEvent) {
                            $iterableValue = $event->value();

                            $renderedIterableEvents[] =
                                '        - Received ' .
                                $this->exporter->export($iterableValue);
                        } elseif ($event instanceof ReceivedExceptionEvent) {
                            $iterableException = $event->exception();

                            $renderedIterableEvents[] =
                                '        - Received exception ' .
                                $this->exporter->export($iterableException);
                        }
                    }

                    $endEvent = $call->endEvent();

                    if (empty($iterableEvents)) {
                        if ($callIsRelevant) {
                            if ($isNever) {
                                $eventResult = $this->pass;
                            } else {
                                $eventResult = $this->fail;
                            }
                        } else {
                            $eventResult = '-';
                        }

                        $renderedIterableEvents[] =
                            '        ' . $eventResult .
                            ' Never started iterating';
                    } elseif ($endEvent instanceof ConsumedEvent) {
                        $renderedIterableEvents[] =
                            '        - Finished iterating';
                    } elseif ($endEvent instanceof ReturnedEvent) {
                        $eventValue = $endEvent->value();

                        $renderedIterableEvents[] =
                            '        - Returned ' .
                            $this->exporter->export($eventValue);
                    } elseif ($endEvent instanceof ThrewEvent) {
                        $eventException = $endEvent->exception();

                        $renderedIterableEvents[] =
                            '        - Threw ' .
                            $this->exporter->export($eventException);
                    } else {
                        $renderedIterableEvents[] =
                            '        - Never finished iterating';
                    }

                    $renderedResponse =
                        'Returned ' . $this->exporter->export($returnValue, 0) .
                        ', then:' . $callEnd . PHP_EOL . $callStart .
                        implode(
                            $callEnd . PHP_EOL . $callStart,
                            $renderedIterableEvents
                        );
                } else {
                    $renderedResponse =
                        'Returned ' . $this->exporter->export($returnValue);
                }
            } elseif ($responseEvent instanceof ThrewEvent) {
                $exception = $responseEvent->exception();
                $renderedResponse =
                    'Threw ' . $this->exporter->export($exception);
            } else {
                $renderedResponse = 'Never responded';
            }

            if (!$isCall && $isMatchingCall) {
                ++$matchCount;
            }

            if ($callIsRelevant) {
                if ($isMatchingCall xor $isNever) {
                    $renderedResult = $this->pass;
                } else {
                    $renderedResult = $this->fail;
                }
            } else {
                $renderedResult = '-';
            }

            $renderedCalls[] =
                $callStart . $renderedResult . ' Call #' . $call->index() .
                ' - ' . $renderedCallee .
                "($renderedArguments):" .
                $callEnd . PHP_EOL .
                $callStart . '    ' . $renderedResult . ' ' .
                $renderedResponse . $callEnd;
        }

        $actual = PHP_EOL . implode(PHP_EOL, $renderedCalls);
        $cardinality = $this->renderCardinality(
            $minimum,
            $maximum,
            $matchCount,
            $totalCount,
            $callCount,
            false
        );

        return $this->reset . $expected . $cardinality . $actual;
    }

    /**
     * Render a failed iterable consumed() verification.
     *
     * @param Spy|Call    $subject     The subject.
     * @param Cardinality $cardinality The cardinality.
     * @param bool        $isGenerator True if this verification is for a generator.
     *
     * @return string The rendered failure message.
     */
    public function renderIterableConsumed(
        $subject,
        Cardinality $cardinality,
        bool $isGenerator
    ): string {
        $isCall = $subject instanceof Call;

        if ($isCall) {
            /** @var Call $subject */

            /** @var array<int,Call> $calls */
            $calls = [$subject];
            /** @var callable $callback */
            $callback = $subject->callback();
            $renderedCallee = $this->exporter->exportCallable($callback);
        } else {
            /** @var Spy $subject */

            $calls = $subject->allCalls();
            $renderedCallee = $this->exporter->exportCallable($subject);
        }

        $renderedSubject = $this->bold . $renderedCallee . $this->reset;

        $minimum = $cardinality->minimum();
        $maximum = $cardinality->maximum();
        $isNever = 0 === $maximum;

        if ($isCall) {
            $totalCount = 1;
            $iterableCount = 1;

            if ($isNever) {
                $iterableResult = $this->fail;
            } else {
                $iterableResult = $this->pass;
            }

            $renderedIterableCount = '';
        } else {
            $totalCount = 0;
            $iterableCount = 0;

            foreach ($calls as $call) {
                ++$totalCount;

                if ($isGenerator) {
                    $isIterable = $call->isGenerator();
                } else {
                    $isIterable = $call->isIterable();
                }

                if ($isIterable) {
                    ++$iterableCount;
                }
            }

            if ($cardinality->matches($iterableCount, $iterableCount)) {
                $iterableResultStart = $this->passStart;
                $iterableResultText = self::PASS;
            } else {
                $iterableResultStart = $this->failStart;
                $iterableResultText = self::FAIL;
            }

            $iterableResult =
                $iterableResultStart .
                $iterableResultText .
                $this->reset;
            $matchOrMatches = 1 === $iterableCount ? 'match' : 'matches';
            $renderedIterableCount =
                ' ' . $iterableResultStart . $this->faint .
                '(' . $iterableCount . ' ' . $matchOrMatches . ')' .
                $this->reset;
        }

        if ($isGenerator) {
            $renderedIterableType = 'Generator';
        } else {
            $renderedIterableType = '<iterable>';
        }

        $renderedCriteria =
            'behave like:' . PHP_EOL .
            '    ' . $iterableResult .
            ' Returned ' . $renderedIterableType .
            ', then:' . $renderedIterableCount . PHP_EOL .
            '        ' . $this->fail . ' Finished iterating';

        if ($isCall) {
            /** @var Call $subject */

            if ($isNever) {
                $expected =
                    'Expected ' . $renderedSubject .
                    ' call #' . $subject->index() .
                    ' not to ' . $renderedCriteria;
            } else {
                $expected =
                    'Expected ' . $renderedSubject .
                    ' call #' . $subject->index() .
                    ' to ' . $renderedCriteria;
            }
        } else {
            /** @var Spy $subject */

            if ($isGenerator) {
                $renderedIterableType = 'generator calls';
            } else {
                $renderedIterableType = 'iterable calls';
            }

            if ($isNever) {
                $expected =
                    'Expected ' . $renderedSubject .
                    ' ' . $renderedIterableType .
                    ' not to ' . $renderedCriteria;
            } elseif ($cardinality->isAlways()) {
                $expected =
                    'Expected all ' . $renderedSubject .
                    ' ' . $renderedIterableType .
                    ' to ' . $renderedCriteria;
            } else {
                $expected =
                    'Expected ' . $renderedSubject .
                    ' ' . $renderedIterableType .
                    ' to ' . $renderedCriteria;
            }
        }

        $renderedCalls = [];
        $matchCount = 0;

        foreach ($calls as $call) {
            if ($isGenerator) {
                $callIsRelevant = $call->isGenerator();
            } else {
                $callIsRelevant = $call->isIterable();
            }

            if ($callIsRelevant) {
                $callStart = '';
                $callEnd = '';
            } else {
                $callStart = $this->faint;
                $callEnd = $this->reset;
            }

            $isMatch = false;
            $renderedArguments = $this->renderCompactCallArguments($call, 0);

            $responseEvent = $call->responseEvent();

            if ($responseEvent instanceof ReturnedEvent) {
                $returnValue = $responseEvent->value();

                if (
                    is_array($returnValue) ||
                    $returnValue instanceof Traversable
                ) {
                    $iterableEvents = $call->iterableEvents();
                    $renderedIterableEvents = [];

                    foreach ($iterableEvents as $event) {
                        if ($event instanceof UsedEvent) {
                            $renderedIterableEvents[] =
                                '        - Started iterating';
                        } elseif ($event instanceof ProducedEvent) {
                            $iterableKey = $event->key();
                            $iterableValue = $event->value();

                            $renderedIterableEvents[] =
                                '        - Produced ' .
                                $this->exporter->export($iterableKey) .
                                ' => ' .
                                $this->exporter->export($iterableValue);
                        } elseif ($event instanceof ReceivedEvent) {
                            $iterableValue = $event->value();

                            $renderedIterableEvents[] =
                                '        - Received ' .
                                $this->exporter->export($iterableValue);
                        } elseif (
                            $event instanceof ReceivedExceptionEvent
                        ) {
                            $iterableException = $event->exception();

                            $renderedIterableEvents[] =
                                '        - Received exception ' .
                                $this->exporter->export($iterableException);
                        }
                    }

                    $endEvent = $call->endEvent();

                    if (empty($iterableEvents)) {
                        if ($callIsRelevant) {
                            if ($isNever) {
                                $eventResult = $this->pass;
                            } else {
                                $eventResult = $this->fail;
                            }
                        } else {
                            $eventResult = '-';
                        }

                        $renderedIterableEvents[] =
                            '        ' . $eventResult .
                            ' Never started iterating';
                    } elseif ($endEvent instanceof ConsumedEvent) {
                        if ($callIsRelevant) {
                            $isMatch = true;

                            if ($isNever) {
                                $eventResult = $this->fail;
                            } else {
                                $eventResult = $this->pass;
                            }
                        } else {
                            $eventResult = '-';
                        }

                        $renderedIterableEvents[] =
                            '        ' . $eventResult . ' Finished iterating';
                    } elseif ($endEvent instanceof ReturnedEvent) {
                        $isMatch = true;
                        $iterableValue = $endEvent->value();

                        if ($isNever) {
                            $eventResult = $this->fail;
                        } else {
                            $eventResult = $this->pass;
                        }

                        $renderedIterableEvents[] =
                            '        ' . $eventResult . ' Returned ' .
                            $this->exporter->export($iterableValue);
                    } elseif ($endEvent instanceof ThrewEvent) {
                        $isMatch = true;

                        if ($isNever) {
                            $eventResult = $this->fail;
                        } else {
                            $eventResult = $this->pass;
                        }

                        $eventException = $endEvent->exception();

                        $renderedIterableEvents[] =
                            '        ' . $eventResult . ' Threw ' .
                            $this->exporter->export($eventException);
                    } else {
                        if ($callIsRelevant) {
                            if ($isNever) {
                                $eventResult = $this->pass;
                            } else {
                                $eventResult = $this->fail;
                            }
                        } else {
                            $eventResult = '-';
                        }

                        $renderedIterableEvents[] =
                            '        ' . $eventResult .
                            ' Never finished iterating';
                    }

                    $renderedResponse =
                        'Returned ' . $this->exporter->export($returnValue, 0) .
                        ', then:' . $callEnd . PHP_EOL . $callStart .
                        implode(
                            $callEnd . PHP_EOL . $callStart,
                            $renderedIterableEvents
                        );
                } else {
                    $renderedResponse =
                        'Returned ' . $this->exporter->export($returnValue);
                }
            } elseif ($responseEvent instanceof ThrewEvent) {
                $exception = $responseEvent->exception();
                $renderedResponse =
                    'Threw ' . $this->exporter->export($exception);
            } else {
                $renderedResponse = 'Never responded';
            }

            if ($isMatch) {
                ++$matchCount;
            }

            if ($callIsRelevant) {
                if ($isMatch xor $isNever) {
                    $renderedResult = $this->pass;
                } else {
                    $renderedResult = $this->fail;
                }
            } else {
                $renderedResult = '-';
            }

            $renderedCalls[] =
                $callStart . $renderedResult . ' Call #' . $call->index() .
                ' - ' . $renderedCallee .
                "($renderedArguments):" .
                $callEnd . PHP_EOL .
                $callStart . '    ' . $renderedResult . ' ' .
                $renderedResponse . $callEnd;
        }

        $actual = PHP_EOL . implode(PHP_EOL, $renderedCalls);

        if ($isCall) {
            $cardinality = '';
        } else {
            $cardinality = $this->renderCardinality(
                $minimum,
                $maximum,
                $matchCount,
                $iterableCount,
                $totalCount,
                false
            );
        }

        return $this->reset . $expected . $cardinality . $actual;
    }

    /**
     * Render a failed generator received() verification.
     *
     * @param Spy|Call    $subject     The subject.
     * @param Cardinality $cardinality The cardinality.
     * @param ?Matcher    $value       The value.
     *
     * @return string The rendered failure message.
     */
    public function renderGeneratorReceived(
        $subject,
        Cardinality $cardinality,
        ?Matcher $value
    ): string {
        $isCall = $subject instanceof Call;

        if ($isCall) {
            /** @var Call $subject */

            /** @var array<int,Call> $calls */
            $calls = [$subject];
            /** @var callable $callback */
            $callback = $subject->callback();
            $renderedCallee = $this->exporter->exportCallable($callback);
        } else {
            /** @var Spy $subject */

            $calls = $subject->allCalls();
            $renderedCallee = $this->exporter->exportCallable($subject);
        }

        $renderedSubject = $this->bold . $renderedCallee . $this->reset;

        $minimum = $cardinality->minimum();
        $maximum = $cardinality->maximum();
        $isNever = 0 === $maximum;

        if ($isCall) {
            /** @var Call $subject */

            $totalCount = 0;
            $callCount = 1;
            $iterableCount = 1;

            foreach ($subject->iterableEvents() as $event) {
                if ($event instanceof ReceivedEvent) {
                    ++$totalCount;
                }
            }

            $renderedIterableCount = '';
        } else {
            /** @var Spy $subject */

            $callCount = 0;
            $iterableCount = 0;

            foreach ($calls as $call) {
                ++$callCount;

                if ($call->isGenerator()) {
                    ++$iterableCount;
                }
            }

            $totalCount = $iterableCount;

            $matchOrMatches = 1 === $iterableCount ? 'match' : 'matches';
            $renderedIterableCount =
                ' ' . $this->faint .
                '(' . $iterableCount .
                ' ' . $matchOrMatches .
                ')' . $this->reset;
        }

        if ($iterableCount xor $isNever) {
            $iterableResult = $this->pass;
        } else {
            $iterableResult = $this->fail;
        }

        if ($value) {
            $renderedValue = $value->describe($this->exporter);
        } else {
            $renderedValue = '<any>';
        }

        $renderedCriteria =
            'behave like:' . PHP_EOL . '    ' . $iterableResult .
            ' Returned Generator, then:' . $renderedIterableCount . PHP_EOL .
            '        ' . $this->fail . ' Received ' . $renderedValue;

        if ($isCall) {
            /** @var Call $subject */

            if ($isNever) {
                $expected =
                    'Expected ' . $renderedSubject .
                    ' call #' . $subject->index() .
                    ' not to ' . $renderedCriteria;
            } else {
                $expected =
                    'Expected ' . $renderedSubject .
                    ' call #' . $subject->index() .
                    ' to ' . $renderedCriteria;
            }
        } else {
            /** @var Spy $subject */

            if ($isNever) {
                $expected =
                    'Expected ' . $renderedSubject .
                    ' generator calls not to ' . $renderedCriteria;
            } elseif ($cardinality->isAlways()) {
                $expected =
                    'Expected all ' . $renderedSubject .
                    ' generator calls to ' . $renderedCriteria;
            } else {
                $expected =
                    'Expected ' . $renderedSubject .
                    ' generator calls to ' . $renderedCriteria;
            }
        }

        $renderedCalls = [];
        $matchCount = 0;

        foreach ($calls as $call) {
            $callIsRelevant = $call->isGenerator();

            if ($callIsRelevant) {
                $callStart = '';
                $callEnd = '';
            } else {
                $callStart = $this->faint;
                $callEnd = $this->reset;
            }

            $isMatchingCall = false;
            $renderedArguments = $this->renderCompactCallArguments($call, 0);

            $responseEvent = $call->responseEvent();

            if ($responseEvent instanceof ReturnedEvent) {
                $returnValue = $responseEvent->value();

                if (
                    is_array($returnValue) ||
                    $returnValue instanceof Traversable
                ) {
                    $iterableEvents = $call->iterableEvents();
                    $renderedIterableEvents = [];

                    foreach ($iterableEvents as $event) {
                        if ($event instanceof UsedEvent) {
                            $renderedIterableEvents[] =
                                '        - Started iterating';
                        } elseif ($event instanceof ProducedEvent) {
                            $iterableKey = $event->key();
                            $iterableValue = $event->value();

                            $renderedIterableEvents[] =
                                '        - Produced ' .
                                $this->exporter->export($iterableKey) .
                                ' => ' .
                                $this->exporter->export($iterableValue);
                        } elseif ($event instanceof ReceivedEvent) {
                            $iterableValue = $event->value();
                            $renderedIterableValue =
                                $this->exporter->export($iterableValue);

                            $eventIsMatch =
                                !$value ||
                                $value->matches($iterableValue);

                            if ($eventIsMatch) {
                                $isMatchingCall = true;

                                if ($isCall) {
                                    ++$matchCount;
                                }
                            } elseif (
                                $value instanceof EqualToMatcher
                            ) {
                                $renderedIterableValue =
                                    $this->differenceEngine->difference(
                                        $renderedValue,
                                        $renderedIterableValue
                                    );
                            }

                            if ($eventIsMatch xor $isNever) {
                                $eventResult = $this->pass;
                            } else {
                                $eventResult = $this->fail;
                            }

                            $renderedIterableEvents[] =
                                '        ' . $eventResult . ' Received ' .
                                $renderedIterableValue;
                        } elseif (
                            $event instanceof ReceivedExceptionEvent
                        ) {
                            $iterableException = $event->exception();

                            $renderedIterableEvents[] =
                                '        - Received exception ' .
                                $this->exporter->export($iterableException);
                        }
                    }

                    $endEvent = $call->endEvent();

                    if (empty($iterableEvents)) {
                        if ($callIsRelevant) {
                            if ($isNever) {
                                $eventResult = $this->pass;
                            } else {
                                $eventResult = $this->fail;
                            }
                        } else {
                            $eventResult = '-';
                        }

                        $renderedIterableEvents[] =
                            '        ' . $eventResult .
                            ' Never started iterating';
                    } elseif ($endEvent instanceof ConsumedEvent) {
                        $renderedIterableEvents[] =
                            '        - Finished iterating';
                    } elseif ($endEvent instanceof ReturnedEvent) {
                        $eventValue = $endEvent->value();

                        $renderedIterableEvents[] =
                            '        - Returned ' .
                            $this->exporter->export($eventValue);
                    } elseif ($endEvent instanceof ThrewEvent) {
                        $eventException = $endEvent->exception();

                        $renderedIterableEvents[] =
                            '        - Threw ' .
                            $this->exporter->export($eventException);
                    } else {
                        $renderedIterableEvents[] =
                            '        - Never finished iterating';
                    }

                    $renderedResponse =
                        'Returned ' . $this->exporter->export($returnValue, 0) .
                        ', then:' . $callEnd . PHP_EOL . $callStart .
                        implode(
                            $callEnd . PHP_EOL . $callStart,
                            $renderedIterableEvents
                        );
                } else {
                    $renderedResponse =
                        'Returned ' . $this->exporter->export($returnValue);
                }
            } elseif ($responseEvent instanceof ThrewEvent) {
                $exception = $responseEvent->exception();
                $renderedResponse =
                    'Threw ' . $this->exporter->export($exception);
            } else {
                $renderedResponse = 'Never responded';
            }

            if (!$isCall && $isMatchingCall) {
                ++$matchCount;
            }

            if ($callIsRelevant) {
                if ($isMatchingCall xor $isNever) {
                    $renderedResult = $this->pass;
                } else {
                    $renderedResult = $this->fail;
                }
            } else {
                $renderedResult = '-';
            }

            $renderedCalls[] =
                $callStart . $renderedResult . ' Call #' . $call->index() .
                ' - ' . $renderedCallee .
                "($renderedArguments):" .
                $callEnd . PHP_EOL .
                $callStart . '    ' . $renderedResult . ' ' .
                $renderedResponse . $callEnd;
        }

        $actual = PHP_EOL . implode(PHP_EOL, $renderedCalls);
        $cardinality = $this->renderCardinality(
            $minimum,
            $maximum,
            $matchCount,
            $totalCount,
            $callCount,
            false
        );

        return $this->reset . $expected . $cardinality . $actual;
    }

    /**
     * Render a failed generator receivedException() verification.
     *
     * @param Spy|Call                      $subject     The subject.
     * @param Cardinality                   $cardinality The cardinality.
     * @param Matcher|Throwable|string|null $type        The type of exception.
     *
     * @return string The rendered failure message.
     */
    public function renderGeneratorReceivedException(
        $subject,
        Cardinality $cardinality,
        $type
    ): string {
        $isCall = $subject instanceof Call;

        if ($isCall) {
            /** @var Call $subject */

            /** @var array<int,Call> $calls */
            $calls = [$subject];
            /** @var callable $callback */
            $callback = $subject->callback();
            $renderedCallee = $this->exporter->exportCallable($callback);
        } else {
            /** @var Spy $subject */

            $calls = $subject->allCalls();
            $renderedCallee = $this->exporter->exportCallable($subject);
        }

        $renderedSubject = $this->bold . $renderedCallee . $this->reset;

        $minimum = $cardinality->minimum();
        $maximum = $cardinality->maximum();
        $isNever = 0 === $maximum;

        if ($isCall) {
            /** @var Call $subject */

            $totalCount = 0;
            $callCount = 1;
            $iterableCount = 1;

            foreach ($subject->iterableEvents() as $event) {
                if ($event instanceof ReceivedExceptionEvent) {
                    ++$totalCount;
                }
            }

            $renderedIterableCount = '';
        } else {
            /** @var Spy $subject */

            $callCount = 0;
            $iterableCount = 0;

            foreach ($calls as $call) {
                ++$callCount;

                if ($call->isGenerator()) {
                    ++$iterableCount;
                }
            }

            $totalCount = $iterableCount;

            $matchOrMatches = 1 === $iterableCount ? 'match' : 'matches';
            $renderedIterableCount =
                ' ' . $this->faint .
                '(' . $iterableCount .
                ' ' . $matchOrMatches .
                ')' . $this->reset;
        }

        if ($iterableCount xor $isNever) {
            $iterableResult = $this->pass;
        } else {
            $iterableResult = $this->fail;
        }

        if ($type instanceof Matcher) {
            $renderedType = $type->describe($this->exporter);
        } elseif (is_string($type)) {
            $renderedType = $type;
        } else {
            $renderedType = '<any>';
        }

        $renderedCriteria =
            'behave like:' . PHP_EOL . '    ' . $iterableResult .
            ' Returned Generator, then:' . $renderedIterableCount . PHP_EOL .
            '        ' . $this->fail . ' Received exception ' . $renderedType;

        if ($isCall) {
            /** @var Call $subject */

            if ($isNever) {
                $expected =
                    'Expected ' . $renderedSubject .
                    ' call #' . $subject->index() .
                    ' not to ' . $renderedCriteria;
            } else {
                $expected =
                    'Expected ' . $renderedSubject .
                    ' call #' . $subject->index() .
                    ' to ' . $renderedCriteria;
            }
        } else {
            /** @var Spy $subject */

            if ($isNever) {
                $expected =
                    'Expected ' . $renderedSubject .
                    ' generator calls not to ' . $renderedCriteria;
            } elseif ($cardinality->isAlways()) {
                $expected =
                    'Expected all ' . $renderedSubject .
                    ' generator calls to ' . $renderedCriteria;
            } else {
                $expected =
                    'Expected ' . $renderedSubject .
                    ' generator calls to ' . $renderedCriteria;
            }
        }

        $renderedCalls = [];
        $matchCount = 0;

        foreach ($calls as $call) {
            $callIsRelevant = $call->isGenerator();

            if ($callIsRelevant) {
                $callStart = '';
                $callEnd = '';
            } else {
                $callStart = $this->faint;
                $callEnd = $this->reset;
            }

            $isMatchingCall = false;
            $renderedArguments = $this->renderCompactCallArguments($call, 0);

            $responseEvent = $call->responseEvent();

            if ($responseEvent instanceof ReturnedEvent) {
                $returnValue = $responseEvent->value();

                if (
                    is_array($returnValue) ||
                    $returnValue instanceof Traversable
                ) {
                    $iterableEvents = $call->iterableEvents();
                    $renderedIterableEvents = [];

                    foreach ($iterableEvents as $event) {
                        if ($event instanceof UsedEvent) {
                            $renderedIterableEvents[] =
                                '        - Started iterating';
                        } elseif ($event instanceof ProducedEvent) {
                            $iterableKey = $event->key();
                            $iterableValue = $event->value();

                            $renderedIterableEvents[] =
                                '        - Produced ' .
                                $this->exporter->export($iterableKey) .
                                ' => ' .
                                $this->exporter->export($iterableValue);
                        } elseif ($event instanceof ReceivedEvent) {
                            $iterableValue = $event->value();

                            $renderedIterableEvents[] =
                                '        - Received ' .
                                $this->exporter->export($iterableValue);
                        } elseif (
                            $event instanceof ReceivedExceptionEvent
                        ) {
                            $iterableException = $event->exception();
                            $renderedIterableException =
                                $this->exporter->export($iterableException);

                            if ($type instanceof Matcher) {
                                $eventIsMatch =
                                    $type->matches($iterableException);
                            } elseif (is_string($type)) {
                                $eventIsMatch =
                                    is_a($iterableException, $type);
                            } else {
                                $eventIsMatch = true;
                            }

                            if ($eventIsMatch) {
                                $isMatchingCall = true;

                                if ($isCall) {
                                    ++$matchCount;
                                }
                            } elseif ($type instanceof EqualToMatcher) {
                                $renderedIterableException =
                                    $this->differenceEngine->difference(
                                        $renderedType,
                                        $renderedIterableException
                                    );
                            }

                            if ($eventIsMatch xor $isNever) {
                                $eventResult = $this->pass;
                            } else {
                                $eventResult = $this->fail;
                            }

                            $renderedIterableEvents[] =
                                '        ' . $eventResult .
                                ' Received exception ' .
                                $renderedIterableException;
                        }
                    }

                    $endEvent = $call->endEvent();

                    if (empty($iterableEvents)) {
                        if ($callIsRelevant) {
                            if ($isNever) {
                                $eventResult = $this->pass;
                            } else {
                                $eventResult = $this->fail;
                            }
                        } else {
                            $eventResult = '-';
                        }

                        $renderedIterableEvents[] =
                            '        ' . $eventResult .
                            ' Never started iterating';
                    } elseif ($endEvent instanceof ConsumedEvent) {
                        $renderedIterableEvents[] =
                            '        - Finished iterating';
                    } elseif ($endEvent instanceof ReturnedEvent) {
                        $eventValue = $endEvent->value();

                        $renderedIterableEvents[] =
                            '        - Returned ' .
                            $this->exporter->export($eventValue);
                    } elseif ($endEvent instanceof ThrewEvent) {
                        $eventException = $endEvent->exception();

                        $renderedIterableEvents[] =
                            '        - Threw ' .
                            $this->exporter->export($eventException);
                    } else {
                        $renderedIterableEvents[] =
                            '        - Never finished iterating';
                    }

                    $renderedResponse =
                        'Returned ' . $this->exporter->export($returnValue, 0) .
                        ', then:' . $callEnd . PHP_EOL . $callStart .
                        implode(
                            $callEnd . PHP_EOL . $callStart,
                            $renderedIterableEvents
                        );
                } else {
                    $renderedResponse =
                        'Returned ' . $this->exporter->export($returnValue);
                }
            } elseif ($responseEvent instanceof ThrewEvent) {
                $exception = $responseEvent->exception();
                $renderedResponse =
                    'Threw ' . $this->exporter->export($exception);
            } else {
                $renderedResponse = 'Never responded';
            }

            if (!$isCall && $isMatchingCall) {
                ++$matchCount;
            }

            if ($callIsRelevant) {
                if ($isMatchingCall xor $isNever) {
                    $renderedResult = $this->pass;
                } else {
                    $renderedResult = $this->fail;
                }
            } else {
                $renderedResult = '-';
            }

            $renderedCalls[] =
                $callStart . $renderedResult . ' Call #' . $call->index() .
                ' - ' . $renderedCallee .
                "($renderedArguments):" .
                $callEnd . PHP_EOL .
                $callStart . '    ' . $renderedResult . ' ' .
                $renderedResponse . $callEnd;
        }

        $actual = PHP_EOL . implode(PHP_EOL, $renderedCalls);
        $cardinality = $this->renderCardinality(
            $minimum,
            $maximum,
            $matchCount,
            $totalCount,
            $callCount,
            false
        );

        return $this->reset . $expected . $cardinality . $actual;
    }

    /**
     * Render a failed generator returned() verification.
     *
     * @param Spy|Call    $subject     The subject.
     * @param Cardinality $cardinality The cardinality.
     * @param ?Matcher    $value       The value.
     *
     * @return string The rendered failure message.
     */
    public function renderGeneratorReturned(
        $subject,
        Cardinality $cardinality,
        ?Matcher $value
    ): string {
        $isCall = $subject instanceof Call;

        if ($isCall) {
            /** @var Call $subject */

            /** @var array<int,Call> $calls */
            $calls = [$subject];
            /** @var callable $callback */
            $callback = $subject->callback();
            $renderedCallee = $this->exporter->exportCallable($callback);
        } else {
            /** @var Spy $subject */

            $calls = $subject->allCalls();
            $renderedCallee = $this->exporter->exportCallable($subject);
        }

        $renderedSubject = $this->bold . $renderedCallee . $this->reset;

        $minimum = $cardinality->minimum();
        $maximum = $cardinality->maximum();
        $isNever = 0 === $maximum;

        if ($isCall) {
            /** @var Call $subject */

            $totalCount = 1;
            $iterableCount = 1;
            $renderedIterableCount = '';
        } else {
            /** @var Spy $subject */

            $iterableCount = 0;

            foreach ($calls as $call) {
                if ($call->isGenerator()) {
                    ++$iterableCount;
                }
            }

            $totalCount = $iterableCount;

            if ($cardinality->matches($iterableCount, $iterableCount)) {
                $iterableResultStart = $this->passStart;
            } else {
                $iterableResultStart = $this->failStart;
            }

            $matchOrMatches = 1 === $iterableCount ? 'match' : 'matches';
            $renderedIterableCount =
                ' ' . $iterableResultStart . $this->faint .
                '(' . $iterableCount . ' ' . $matchOrMatches . ')' .
                $this->reset;
        }

        if ($iterableCount xor $isNever) {
            $iterableResult = $this->pass;
        } else {
            $iterableResult = $this->fail;
        }

        if ($value) {
            $renderedValue = $value->describe($this->exporter);
        } else {
            $renderedValue = '<any>';
        }

        $renderedCriteria =
            'behave like:' . PHP_EOL . '    ' . $iterableResult .
            ' Returned Generator, then:' . $renderedIterableCount . PHP_EOL .
            '        ' . $this->fail . ' Returned ' . $renderedValue;

        if ($isCall) {
            /** @var Call $subject */

            if ($isNever) {
                $expected =
                    'Expected ' . $renderedSubject .
                    ' call #' . $subject->index() .
                    ' not to ' . $renderedCriteria;
            } else {
                $expected =
                    'Expected ' . $renderedSubject .
                    ' call #' . $subject->index() .
                    ' to ' . $renderedCriteria;
            }
        } else {
            /** @var Spy $subject */

            if ($isNever) {
                $expected =
                    'Expected ' . $renderedSubject .
                    ' generator calls not to ' . $renderedCriteria;
            } elseif ($cardinality->isAlways()) {
                $expected =
                    'Expected all ' . $renderedSubject .
                    ' generator calls to ' . $renderedCriteria;
            } else {
                $expected =
                    'Expected ' . $renderedSubject .
                    ' generator calls to ' . $renderedCriteria;
            }
        }

        $renderedCalls = [];
        $matchCount = 0;

        foreach ($calls as $call) {
            $callIsRelevant = $call->isGenerator();

            if ($callIsRelevant) {
                $callStart = '';
                $callEnd = '';
            } else {
                $callStart = $this->faint;
                $callEnd = $this->reset;
            }

            $isMatchingCall = false;
            $renderedArguments = $this->renderCompactCallArguments($call, 0);

            $responseEvent = $call->responseEvent();

            if ($responseEvent instanceof ReturnedEvent) {
                $returnValue = $responseEvent->value();

                if (
                    is_array($returnValue) ||
                    $returnValue instanceof Traversable
                ) {
                    $iterableEvents = $call->iterableEvents();
                    $renderedIterableEvents = [];

                    foreach ($iterableEvents as $event) {
                        if ($event instanceof UsedEvent) {
                            $renderedIterableEvents[] =
                                '        - Started iterating';
                        } elseif ($event instanceof ProducedEvent) {
                            $iterableKey = $event->key();
                            $iterableValue = $event->value();

                            $renderedIterableEvents[] =
                                '        - Produced ' .
                                $this->exporter->export($iterableKey) .
                                ' => ' .
                                $this->exporter->export($iterableValue);
                        } elseif ($event instanceof ReceivedEvent) {
                            $iterableValue = $event->value();

                            $renderedIterableEvents[] =
                                '        - Received ' .
                                $this->exporter->export($iterableValue);
                        } elseif (
                            $event instanceof ReceivedExceptionEvent
                        ) {
                            $iterableException = $event->exception();

                            $renderedIterableEvents[] =
                                '        - Received exception ' .
                                $this->exporter->export($iterableException);
                        }
                    }

                    $endEvent = $call->endEvent();

                    if (empty($iterableEvents)) {
                        if ($callIsRelevant) {
                            if ($isNever) {
                                $eventResult = $this->pass;
                            } else {
                                $eventResult = $this->fail;
                            }
                        } else {
                            $eventResult = '-';
                        }

                        $renderedIterableEvents[] =
                            '        ' . $eventResult .
                            ' Never started iterating';
                    } elseif ($endEvent instanceof ConsumedEvent) {
                        $renderedIterableEvents[] =
                            '        - Finished iterating';
                    } elseif ($endEvent instanceof ReturnedEvent) {
                        $iterableValue = $endEvent->value();
                        $renderedIterableValue =
                            $this->exporter->export($iterableValue);

                        $eventIsMatch =
                            !$value || $value->matches($iterableValue);

                        if ($eventIsMatch) {
                            ++$matchCount;
                            $isMatchingCall = true;
                        } elseif ($value instanceof EqualToMatcher) {
                            $renderedIterableValue =
                                $this->differenceEngine->difference(
                                    $renderedValue,
                                    $renderedIterableValue
                                );
                        }

                        if ($eventIsMatch xor $isNever) {
                            $eventResult = $this->pass;
                        } else {
                            $eventResult = $this->fail;
                        }

                        $renderedIterableEvents[] =
                            '        ' . $eventResult . ' Returned ' .
                            $renderedIterableValue;
                    } elseif ($endEvent instanceof ThrewEvent) {
                        if ($isNever) {
                            $eventResult = $this->pass;
                        } else {
                            $eventResult = $this->fail;
                        }

                        $eventException = $endEvent->exception();

                        $renderedIterableEvents[] =
                            '        ' . $eventResult . ' Threw ' .
                            $this->exporter->export($eventException);
                    } else {
                        if ($callIsRelevant) {
                            if ($isNever) {
                                $eventResult = $this->pass;
                            } else {
                                $eventResult = $this->fail;
                            }
                        } else {
                            $eventResult = '-';
                        }

                        $renderedIterableEvents[] =
                            '        ' . $eventResult .
                            ' Never finished iterating';
                    }

                    $renderedResponse =
                        'Returned ' . $this->exporter->export($returnValue, 0) .
                        ', then:' . $callEnd . PHP_EOL . $callStart .
                        implode(
                            $callEnd . PHP_EOL . $callStart,
                            $renderedIterableEvents
                        );
                } else {
                    $renderedResponse =
                        'Returned ' . $this->exporter->export($returnValue);
                }
            } elseif ($responseEvent instanceof ThrewEvent) {
                $exception = $responseEvent->exception();
                $renderedResponse =
                    'Threw ' . $this->exporter->export($exception);
            } else {
                $renderedResponse = 'Never responded';
            }

            if ($callIsRelevant) {
                if ($isMatchingCall xor $isNever) {
                    $renderedResult = $this->pass;
                } else {
                    $renderedResult = $this->fail;
                }
            } else {
                $renderedResult = '-';
            }

            $renderedCalls[] =
                $callStart . $renderedResult . ' Call #' . $call->index() .
                ' - ' . $renderedCallee .
                "($renderedArguments):" .
                $callEnd . PHP_EOL .
                $callStart . '    ' . $renderedResult . ' ' .
                $renderedResponse . $callEnd;
        }

        $actual = PHP_EOL . implode(PHP_EOL, $renderedCalls);

        if ($isCall) {
            $cardinality = '';
        } else {
            $cardinality = $this->renderCardinality(
                $minimum,
                $maximum,
                $matchCount,
                $totalCount,
                $totalCount,
                false
            );
        }

        return $this->reset . $expected . $cardinality . $actual;
    }

    /**
     * Render a failed generator threw() verification.
     *
     * @param Spy|Call                      $subject     The subject.
     * @param Cardinality                   $cardinality The cardinality.
     * @param Matcher|Throwable|string|null $type        The type of exception.
     *
     * @return string The rendered failure message.
     */
    public function renderGeneratorThrew(
        $subject,
        Cardinality $cardinality,
        $type
    ): string {
        $isCall = $subject instanceof Call;

        if ($isCall) {
            /** @var Call $subject */

            /** @var array<int,Call> $calls */
            $calls = [$subject];
            /** @var callable $callback */
            $callback = $subject->callback();
            $renderedCallee = $this->exporter->exportCallable($callback);
        } else {
            /** @var Spy $subject */

            $calls = $subject->allCalls();
            $renderedCallee = $this->exporter->exportCallable($subject);
        }

        $renderedSubject = $this->bold . $renderedCallee . $this->reset;

        $minimum = $cardinality->minimum();
        $maximum = $cardinality->maximum();
        $isNever = 0 === $maximum;

        if ($isCall) {
            $totalCount = 1;
            $iterableCount = 1;
            $renderedIterableCount = '';
        } else {
            $iterableCount = 0;

            foreach ($calls as $call) {
                if ($call->isGenerator()) {
                    ++$iterableCount;
                }
            }

            $totalCount = $iterableCount;

            if ($cardinality->matches($iterableCount, $iterableCount)) {
                $iterableResultStart = $this->passStart;
            } else {
                $iterableResultStart = $this->failStart;
            }

            $matchOrMatches = 1 === $iterableCount ? 'match' : 'matches';
            $renderedIterableCount =
                ' ' . $iterableResultStart . $this->faint .
                '(' . $iterableCount . ' ' . $matchOrMatches . ')' .
                $this->reset;
        }

        if ($iterableCount xor $isNever) {
            $iterableResult = $this->pass;
        } else {
            $iterableResult = $this->fail;
        }

        if ($type instanceof Matcher) {
            $renderedType = $type->describe($this->exporter);
        } elseif (is_string($type)) {
            $renderedType = $type;
        } else {
            $renderedType = '<any>';
        }

        $renderedCriteria =
            'behave like:' . PHP_EOL . '    ' . $iterableResult .
            ' Returned Generator, then:' . $renderedIterableCount . PHP_EOL .
            '        ' . $this->fail . ' Threw ' . $renderedType;

        if ($isCall) {
            /** @var Call $subject */

            if ($isNever) {
                $expected =
                    'Expected ' . $renderedSubject .
                    ' call #' . $subject->index() .
                    ' not to ' . $renderedCriteria;
            } else {
                $expected =
                    'Expected ' . $renderedSubject .
                    ' call #' . $subject->index() .
                    ' to ' . $renderedCriteria;
            }
        } else {
            /** @var Spy $subject */

            if ($isNever) {
                $expected =
                    'Expected ' . $renderedSubject .
                    ' generator calls not to ' . $renderedCriteria;
            } elseif ($cardinality->isAlways()) {
                $expected =
                    'Expected all ' . $renderedSubject .
                    ' generator calls to ' . $renderedCriteria;
            } else {
                $expected =
                    'Expected ' . $renderedSubject .
                    ' generator calls to ' . $renderedCriteria;
            }
        }

        $renderedCalls = [];
        $matchCount = 0;

        foreach ($calls as $call) {
            $callIsRelevant = $call->isGenerator();

            if ($callIsRelevant) {
                $callStart = '';
                $callEnd = '';
            } else {
                $callStart = $this->faint;
                $callEnd = $this->reset;
            }

            $isMatchingCall = false;
            $renderedArguments = $this->renderCompactCallArguments($call, 0);

            $responseEvent = $call->responseEvent();

            if ($responseEvent instanceof ReturnedEvent) {
                $returnValue = $responseEvent->value();

                if (
                    is_array($returnValue) ||
                    $returnValue instanceof Traversable
                ) {
                    $iterableEvents = $call->iterableEvents();
                    $renderedIterableEvents = [];

                    foreach ($iterableEvents as $event) {
                        if ($event instanceof UsedEvent) {
                            $renderedIterableEvents[] =
                                '        - Started iterating';
                        } elseif ($event instanceof ProducedEvent) {
                            $iterableKey = $event->key();
                            $iterableValue = $event->value();

                            $renderedIterableEvents[] =
                                '        - Produced ' .
                                $this->exporter->export($iterableKey) .
                                ' => ' .
                                $this->exporter->export($iterableValue);
                        } elseif ($event instanceof ReceivedEvent) {
                            $iterableValue = $event->value();

                            $renderedIterableEvents[] =
                                '        - Received ' .
                                $this->exporter->export($iterableValue);
                        } elseif (
                            $event instanceof ReceivedExceptionEvent
                        ) {
                            $iterableException = $event->exception();

                            $renderedIterableEvents[] =
                                '        - Received exception ' .
                                $this->exporter->export($iterableException);
                        }
                    }

                    $endEvent = $call->endEvent();

                    if (empty($iterableEvents)) {
                        if ($callIsRelevant) {
                            if ($isNever) {
                                $eventResult = $this->pass;
                            } else {
                                $eventResult = $this->fail;
                            }
                        } else {
                            $eventResult = '-';
                        }

                        $renderedIterableEvents[] =
                            '        ' . $eventResult .
                            ' Never started iterating';
                    } elseif ($endEvent instanceof ConsumedEvent) {
                        $renderedIterableEvents[] =
                            '        - Finished iterating';
                    } elseif ($endEvent instanceof ReturnedEvent) {
                        $iterableValue = $endEvent->value();

                        if ($isNever) {
                            $eventResult = $this->pass;
                        } else {
                            $eventResult = $this->fail;
                        }

                        $renderedIterableEvents[] =
                            '        ' . $eventResult . ' Returned ' .
                            $this->exporter->export($iterableValue);
                    } elseif ($endEvent instanceof ThrewEvent) {
                        $iterableException = $endEvent->exception();
                        $renderedIterableException =
                            $this->exporter->export($iterableException);

                        if ($type instanceof Matcher) {
                            $eventIsMatch =
                                $type->matches($iterableException);
                        } elseif (is_string($type)) {
                            $eventIsMatch = is_a($iterableException, $type);
                        } else {
                            $eventIsMatch = true;
                        }

                        if ($eventIsMatch) {
                            ++$matchCount;
                            $isMatchingCall = true;
                        } elseif ($type instanceof EqualToMatcher) {
                            $renderedIterableException =
                                $this->differenceEngine->difference(
                                    $renderedType,
                                    $renderedIterableException
                                );
                        }

                        if ($eventIsMatch xor $isNever) {
                            $eventResult = $this->pass;
                        } else {
                            $eventResult = $this->fail;
                        }

                        $renderedIterableEvents[] =
                            '        ' . $eventResult .
                            ' Threw ' . $renderedIterableException;
                    } else {
                        if ($callIsRelevant) {
                            if ($isNever) {
                                $eventResult = $this->pass;
                            } else {
                                $eventResult = $this->fail;
                            }
                        } else {
                            $eventResult = '-';
                        }

                        $renderedIterableEvents[] =
                            '        ' . $eventResult .
                            ' Never finished iterating';
                    }

                    $renderedResponse =
                        'Returned ' . $this->exporter->export($returnValue, 0) .
                        ', then:' . $callEnd . PHP_EOL . $callStart .
                        implode(
                            $callEnd . PHP_EOL . $callStart,
                            $renderedIterableEvents
                        );
                } else {
                    $renderedResponse =
                        'Returned ' . $this->exporter->export($returnValue);
                }
            } elseif ($responseEvent instanceof ThrewEvent) {
                $exception = $responseEvent->exception();
                $renderedResponse =
                    'Threw ' . $this->exporter->export($exception);
            } else {
                $renderedResponse = 'Never responded';
            }

            if ($callIsRelevant) {
                if ($isMatchingCall xor $isNever) {
                    $renderedResult = $this->pass;
                } else {
                    $renderedResult = $this->fail;
                }
            } else {
                $renderedResult = '-';
            }

            $renderedCalls[] =
                $callStart . $renderedResult . ' Call #' . $call->index() .
                ' - ' . $renderedCallee .
                "($renderedArguments):" .
                $callEnd . PHP_EOL .
                $callStart . '    ' . $renderedResult . ' ' .
                $renderedResponse . $callEnd;
        }

        $actual = PHP_EOL . implode(PHP_EOL, $renderedCalls);

        if ($isCall) {
            $cardinality = '';
        } else {
            $cardinality = $this->renderCardinality(
                $minimum,
                $maximum,
                $matchCount,
                $totalCount,
                $totalCount,
                false
            );
        }

        return $this->reset . $expected . $cardinality . $actual;
    }

    /**
     * Render a failed noInteraction() verification.
     *
     * @param Handle          $handle The handle.
     * @param array<int,Call> $calls  The calls.
     *
     * @return string The rendered failure message.
     */
    public function renderNoInteraction(Handle $handle, array $calls): string
    {
        $class = $handle->class();

        if ($parentClass = $class->getParentClass()) {
            $class = $parentClass;
        }

        $atoms = explode('\\', $class->getName());
        $renderedHandle = array_pop($atoms);

        if ($handle instanceof InstanceHandle) {
            $label = $handle->label();

            if ('' !== $label) {
                $renderedHandle .= '[' . $label . ']';
            }
        } else {
            $renderedHandle .= '[static]';
        }

        usort($calls, [CallData::class, 'compareSequential']);
        $renderedCalls = [];

        foreach ($calls as $call) {
            $renderedArguments = $this->renderCompactCallArguments($call);

            $renderedCalls[] =
                '    ' . $this->fail .
                ' ' . $this->exporter->exportCallable($call->callback()) .
                "($renderedArguments)";
        }

        return $this->reset . 'Expected no interaction with ' .
            $this->bold . $renderedHandle . $this->reset .
            '. Calls:' . PHP_EOL . implode(PHP_EOL, $renderedCalls);
    }

    /**
     * Render a failed inOrder() verification.
     *
     * @param array<int,Event> $expected The expected events.
     * @param array<int,Event> $actual   The actual events.
     *
     * @return string The rendered failure message.
     */
    public function renderInOrder(array $expected, array $actual): string
    {
        if (empty($expected)) {
            return $this->reset . 'Expected events.' . PHP_EOL .
                $this->failStart . 'No events recorded.' . $this->reset;
        }

        $from = $this->renderEvents($expected);
        $to = $this->renderEvents($actual);

        $matcher = new DifferenceSequenceMatcher($from, $to);
        $diff = [];

        foreach ($matcher->getOpcodes() as $opcode) {
            list($tag, $i1, $i2, $j1, $j2) = $opcode;

            if ($tag === 'equal') {
                foreach (array_slice($from, $i1, $i2 - $i1) as $event) {
                    $diff[] = '    ' . $this->pass . '   ' . $event;
                }
            } else {
                if ($tag === 'replace' || $tag === 'delete') {
                    foreach (array_slice($from, $i1, $i2 - $i1) as $event) {
                        $diff[] =
                            '    ' . $this->fail . ' ' .
                            $this->removeStart . $event . $this->removeEnd;
                    }
                }

                if ($tag === 'replace' || $tag === 'insert') {
                    foreach (array_slice($to, $j1, $j2 - $j1) as $event) {
                        $diff[] =
                            '    - ' . $this->addStart . $event . $this->addEnd;
                    }
                }
            }
        }

        $renderedExpected = [];

        foreach ($from as $event) {
            $renderedExpected[] = '    - ' . $event;
        }

        $renderedActual = [];

        foreach ($to as $event) {
            $renderedActual[] = '    - ' . $event;
        }

        return $this->reset . 'Expected events in order:' . PHP_EOL .
            implode(PHP_EOL, $renderedExpected) . PHP_EOL .
            'Actual order:' . PHP_EOL .
            implode(PHP_EOL, $renderedActual) . PHP_EOL .
            'Difference:' . PHP_EOL .
            implode(PHP_EOL, $diff);
    }

    /**
     * Render a value.
     *
     * @param mixed $value The value.
     *
     * @return string The rendered value.
     */
    public function renderValue($value): string
    {
        return $this->exporter->export($value);
    }

    /**
     * Render a matcher set.
     *
     * @param MatcherSet $matcherSet The matcher set.
     *
     * @return string The rendered matcher set.
     */
    public function renderMatcherSet(MatcherSet $matcherSet): string
    {
        $matchers = [];

        foreach ($matcherSet->parameterNames as $position => $name) {
            $matcher = $matcherSet->declaredMatchers[$position];

            if ($matcher) {
                $matchers[] = "$name: " . $matcher->describe($this->exporter);
            } else {
                $matchers[] = "$name: <omitted>";
            }
        }

        foreach ($matcherSet->variadicMatchers as $positionOrName => $matcher) {
            $matchers[] =
                "$positionOrName: " . $matcher->describe($this->exporter);
        }

        if ($matcherSet->wildcardMatcher) {
            $matchers[] =
                $matcherSet->wildcardMatcher->describe($this->exporter);
        }

        if ($matchers) {
            return join(', ', $matchers);
        }

        return '<none>';
    }

    /**
     * @param array<int,Event> $events
     *
     * @return array<int,string>
     */
    private function renderEvents(array $events): array
    {
        $rendered = [];

        foreach ($events as $event) {
            if ($event instanceof CallEvent) {
                /** @var Call */
                $call = $event->call();
                $renderedArguments = $this->renderCompactCallArguments($call);

                $call =
                    $this->exporter->exportCallable($call->callback()) .
                    "($renderedArguments)";
            } else {
                $call = null;
            }

            if ($event instanceof Call) {
                $renderedArguments = $this->renderCompactCallArguments($event);

                $rendered[] =
                    'Called ' .
                    $this->exporter->exportCallable($event->callback()) .
                    "($renderedArguments)";
            } elseif ($event instanceof CalledEvent) {
                $rendered[] = 'Called ' . $call;
            } elseif ($event instanceof ReturnedEvent) {
                $eventValue = $event->value();

                $rendered[] =
                    'Returned ' . $this->exporter->export($eventValue) .
                    ' from ' . $call;
            } elseif ($event instanceof ThrewEvent) {
                $eventException = $event->exception();

                $rendered[] =
                    'Threw ' . $this->exporter->export($eventException) .
                    ' from ' . $call;
            } elseif ($event instanceof UsedEvent) {
                $rendered[] = $call . ' started iterating';
            } elseif ($event instanceof ProducedEvent) {
                $eventKey = $event->key();
                $eventValue = $event->value();

                $rendered[] =
                    'Produced ' . $this->exporter->export($eventKey) .
                    ' => ' . $this->exporter->export($eventValue) .
                    ' from ' . $call;
            } elseif ($event instanceof ReceivedEvent) {
                $eventValue = $event->value();

                $rendered[] =
                    'Received ' . $this->exporter->export($eventValue) .
                    ' in ' . $call;
            } elseif ($event instanceof ReceivedExceptionEvent) {
                $eventException = $event->exception();

                $rendered[] =
                    'Received exception ' .
                    $this->exporter->export($eventException) . ' in ' . $call;
            } elseif ($event instanceof ConsumedEvent) {
                $rendered[] = $call . ' finished iterating';
            } else {
                $eventClass = get_class($event);

                $rendered[] = $this->exporter->export($eventClass) . ' event';
            }
        }

        return $rendered;
    }

    private function renderCardinality(
        int $minimum,
        int $maximum,
        int $matchCount,
        int $totalCount,
        int $callCount,
        bool $isFailureCause
    ): string {
        if (!$minimum) {
            if (0 === $maximum) {
                $expected = '';
            } else {
                $expected = 'Up to ' . $maximum . ' allowed. ';
            }
        } elseif ($maximum < 0) {
            if (1 === $minimum) {
                $expected = '';
            } else {
                $expected = 'At least ' . $minimum . ' required. ';
            }
        } elseif ($minimum === $maximum) {
            $expected = 'Exactly ' . $minimum . ' required. ';
        } else {
            $expected =
                'Between ' . $minimum . ' and ' . $maximum . ' allowed. ';
        }

        if ($callCount) {
            $actual = 'Matched ' . $matchCount . ' of ' . $totalCount . ':';
        } else {
            $isFailureCause = true;
            $actual = 'Never called.';
        }

        if ($isFailureCause || $expected) {
            return
                PHP_EOL . $this->failStart . $expected . $actual . $this->reset;
        }

        return PHP_EOL . $expected . $actual;
    }

    private function renderCompactCallArguments(
        Call $call,
        int $depth = null
    ): string {
        $parameterNames = [];

        foreach ($call->parameters() as $parameter) {
            if (!$parameter->isVariadic()) {
                $parameterNames[] = $parameter->getName();
            }
        }

        $arguments = $this->argumentNormalizer
            ->normalize($parameterNames, $call->arguments()->all());
        $rendered = [];
        $hasNamed = false;

        foreach ($arguments as $positionOrName => &$value) {
            if ($hasNamed || is_string($positionOrName)) {
                $hasNamed = true;
                $key = "$positionOrName: ";
            } else {
                $key = '';
            }

            $rendered[] = $key . $this->exporter->export($value, $depth);
        }

        return join(', ', $rendered);
    }

    const PASS = "\u{2713}";
    const FAIL = "\u{2717}";

    /**
     * @var MatcherVerifier
     */
    private $matcherVerifier;

    /**
     * @var Exporter
     */
    private $exporter;

    /**
     * @var DifferenceEngine
     */
    private $differenceEngine;

    /**
     * @var FeatureDetector
     */
    private $featureDetector;

    /**
     * @var ArgumentNormalizer
     */
    private $argumentNormalizer;

    /**
     * @var string
     */
    private $reset;

    /**
     * @var string
     */
    private $bold;

    /**
     * @var string
     */
    private $faint;

    /**
     * @var string
     */
    private $passStart;

    /**
     * @var string
     */
    private $failStart;

    /**
     * @var string
     */
    private $pass;

    /**
     * @var string
     */
    private $fail;

    /**
     * @var string
     */
    private $addStart;

    /**
     * @var string
     */
    private $addEnd;

    /**
     * @var string
     */
    private $removeStart;

    /**
     * @var string
     */
    private $removeEnd;

    private static function maxMatcherSetKeyLength(MatcherSet $matcherSet): int
    {
        $max = $matcherSet->wildcardMatcher ? 3 : 0;

        foreach ($matcherSet->declaredMatchers as $position => $matcher) {
            if ($matcher) {
                $length = strlen($matcherSet->parameterNames[$position]);

                if ($length > $max) {
                    $max = $length;
                }
            }
        }

        foreach ($matcherSet->variadicMatchers as $positionOrName => $matcher) {
            $length = strlen((string) $positionOrName);

            if ($length > $max) {
                $max = $length;
            }
        }

        return $max;
    }

    private static function maxArgumentKeyLength(Arguments $arguments): int
    {
        $max = 0;

        foreach ($arguments->all() as $positionOrName => $argument) {
            $length = strlen((string) $positionOrName);

            if ($length > $max) {
                $max = $length;
            }
        }

        return $max;
    }
}
