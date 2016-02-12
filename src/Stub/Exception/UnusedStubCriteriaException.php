<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2016 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Stub\Exception;

use Eloquent\Phony\Assertion\Renderer\AssertionRenderer;
use Eloquent\Phony\Matcher\MatcherInterface;
use Exception;

/**
 * Stub criteria were specified, but never used.
 */
final class UnusedStubCriteriaException extends Exception
{
    /**
     * Construct a new unused stub criteria exception.
     *
     * @param array<MatcherInterface> $criteria The criteria.
     * @param Exception|null          $cause    The cause, if available.
     */
    public function __construct(array $criteria, Exception $cause = null)
    {
        $this->criteria = $criteria;

        parent::__construct(
            sprintf(
                'Stub criteria %s were never used. ' .
                    'Check for incomplete stub rules.',
                var_export(
                    AssertionRenderer::instance()->renderMatchers($criteria),
                    true
                )
            ),
            0,
            $cause
        );
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

    private $criteria;
}
