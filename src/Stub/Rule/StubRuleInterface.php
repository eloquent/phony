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

use Eloquent\Phony\Matcher\MatcherInterface;
use Eloquent\Phony\Stub\Answer\AnswerInterface;
use Eloquent\Phony\Stub\Rule\Exception\UndefinedAnswerException;

/**
 * The interface implemented by stub rules.
 */
interface StubRuleInterface
{
    /**
     * Get the criteria.
     *
     * @return array<MatcherInterface> The criteria.
     */
    public function criteria();

    /**
     * Get the answers.
     *
     * @return array<AnswerInterface> The answers.
     */
    public function answers();

    /**
     * Get the next answer.
     *
     * @return AnswerInterface          The answer.
     * @throws UndefinedAnswerException If an undefined or incomplete answer is encountered.
     */
    public function next();
}
