<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2015 Erin Millard
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
 *
 * @internal
 */
class StubRule implements StubRuleInterface
{
    /**
     * Construct a new stub rule.
     *
     * @param array<integer,MatcherInterface> $criteria        The criteria.
     * @param MatcherVerifierInterface|null   $matcherVerifier The matcher verifier to use.
     */
    public function __construct(
        array $criteria,
        MatcherVerifierInterface $matcherVerifier = null
    ) {
        if (null === $matcherVerifier) {
            $matcherVerifier = MatcherVerifier::instance();
        }

        $this->criteria = $criteria;
        $this->matcherVerifier = $matcherVerifier;
        $this->answers = array();
        $this->answerCount = 0;
        $this->calledCount = 0;
    }

    /**
     * Get the criteria.
     *
     * @return array<integer,MatcherInterface> The criteria.
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
     * Add an answer.
     *
     * @param AnswerInterface $answer The answer.
     */
    public function addAnswer(AnswerInterface $answer)
    {
        $this->answers[] = $answer;
        $this->answerCount++;
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
     * @param ArgumentsInterface|array<integer,mixed> $arguments The arguments.
     *
     * @return boolean True if the criteria matches.
     */
    public function matches($arguments)
    {
        return $this->matcherVerifier
            ->matches($this->criteria, Arguments::adapt($arguments));
    }

    /**
     * Get the next answer.
     *
     * @return AnswerInterface          The answer.
     * @throws UndefinedAnswerException If an undefined or incomplete answer is encountered.
     */
    public function next()
    {
        if ($this->calledCount > $this->answerCount - 1) {
            $index = $this->answerCount - 1;
        } else {
            $index = $this->calledCount;
        }

        $this->calledCount++;

        if (
            !isset($this->answers[$index]) ||
            !$this->answers[$index]->primaryRequest()
        ) {
            throw new UndefinedAnswerException();
        }

        return $this->answers[$index];
    }

    private $criteria;
    private $matcherVerifier;
    private $answers;
    private $answerCount;
    private $calledCount;
}
