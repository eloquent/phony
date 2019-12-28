<?php

declare(strict_types=1);

namespace Eloquent\Phony\Stub\Exception;

use Eloquent\Phony\Assertion\AssertionRenderer;
use Eloquent\Phony\Matcher\Matcher;
use Exception;

/**
 * Stub criteria were specified, but never used.
 */
final class UnusedStubCriteriaException extends Exception
{
    /**
     * Construct a new unused stub criteria exception.
     *
     * @param array<int,Matcher> $criteria The criteria.
     */
    public function __construct(array $criteria)
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
            )
        );
    }

    /**
     * Get the criteria.
     *
     * @return array<int,Matcher> The criteria.
     */
    public function criteria(): array
    {
        return $this->criteria;
    }

    /**
     * @var array<int,Matcher>
     */
    private $criteria;
}
