<?php

declare(strict_types=1);

namespace Eloquent\Phony\Stub;

use Eloquent\Phony\Matcher\MatcherSet;
use Eloquent\Phony\Stub\Answer\Answer;
use Eloquent\Phony\Stub\Exception\UndefinedAnswerException;

/**
 * Represents a set of criteria and associated answers.
 */
class StubRule
{
    /**
     * Construct a new stub rule.
     *
     * @param MatcherSet        $criteria The criteria.
     * @param array<int,Answer> $answers  The answers.
     */
    public function __construct(MatcherSet $criteria, array $answers)
    {
        $this->criteria = $criteria;
        $this->answers = $answers;

        $this->lastIndex = count($answers) - 1;
        $this->calledCount = 0;
    }

    /**
     * Get the criteria.
     *
     * @return MatcherSet The criteria.
     */
    public function criteria(): MatcherSet
    {
        return $this->criteria;
    }

    /**
     * Get the answers.
     *
     * @return array<int,Answer> The answers.
     */
    public function answers(): array
    {
        return $this->answers;
    }

    /**
     * Get the next answer.
     *
     * @return Answer                   The answer.
     * @throws UndefinedAnswerException If an undefined or incomplete answer is encountered.
     */
    public function next(): Answer
    {
        if ($this->calledCount > $this->lastIndex) {
            $index = $this->lastIndex;
        } else {
            $index = $this->calledCount;
        }

        ++$this->calledCount;

        if (!isset($this->answers[$index])) {
            throw new UndefinedAnswerException();
        }

        return $this->answers[$index];
    }

    /**
     * @var MatcherSet
     */
    private $criteria;

    /**
     * @var array<int,Answer>
     */
    private $answers;

    /**
     * @var int
     */
    private $lastIndex;

    /**
     * @var int
     */
    private $calledCount;
}
