<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2016 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Stub\Rule;

use Eloquent\Phony\Call\Argument\Arguments;
use Eloquent\Phony\Call\Argument\ArgumentsInterface;
use Eloquent\Phony\Matcher\Verification\MatcherVerifier;
use Eloquent\Phony\Matcher\Verification\MatcherVerifierInterface;
use Eloquent\Phony\Stub\Answer\AnswerInterface;
use Eloquent\Phony\Stub\Rule\Exception\UndefinedAnswerException;

/**
 * Represents a set of criteria and associated answers.
 */
class StubRule implements StubRuleInterface
{
    /**
     * Construct a new stub rule.
     *
     * @param array<MatcherInterface>       $criteria        The criteria.
     * @param array<AnswerInterface>        $answers         The answers.
     * @param MatcherVerifierInterface|null $matcherVerifier The matcher verifier to use.
     */
    public function __construct(
        array $criteria,
        array $answers,
        MatcherVerifierInterface $matcherVerifier = null
    ) {
        if (null === $matcherVerifier) {
            $matcherVerifier = MatcherVerifier::instance();
        }

        $this->criteria = $criteria;
        $this->answers = $answers;
        $this->matcherVerifier = $matcherVerifier;

        $this->lastIndex = count($answers) - 1;
        $this->calledCount = 0;
    }

    /**
     * Get the criteria.
     *
     * @return array<MatcherInterface> The criteria.
     */
    public function criteria()
    {
        return $this->criteria;
    }

    /**
     * Get the matcher verifier.
     *
     * @return MatcherVerifierInterface The matcher verifier.
     */
    public function matcherVerifier()
    {
        return $this->matcherVerifier;
    }

    /**
     * Get the answers.
     *
     * @return array<AnswerInterface> The answers.
     */
    public function answers()
    {
        return $this->answers;
    }

    /**
     * Returns true if this rule's criteria match the supplied arguments.
     *
     * @param ArgumentsInterface|array $arguments The arguments.
     *
     * @return boolean True if the criteria matches.
     */
    public function matches($arguments)
    {
        return $this->matcherVerifier->matches($this->criteria, $arguments);
    }

    /**
     * Get the next answer.
     *
     * @return AnswerInterface          The answer.
     * @throws UndefinedAnswerException If an undefined or incomplete answer is encountered.
     */
    public function next()
    {
        if ($this->calledCount > $this->lastIndex) {
            $index = $this->lastIndex;
        } else {
            $index = $this->calledCount;
        }

        ++$this->calledCount;

        if (
            !isset($this->answers[$index]) ||
            !$this->answers[$index]->primaryRequest()
        ) {
            throw new UndefinedAnswerException();
        }

        return $this->answers[$index];
    }

    private $criteria;
    private $answers;
    private $matcherVerifier;
    private $lastIndex;
    private $calledCount;
}
