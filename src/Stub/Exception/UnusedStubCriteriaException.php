<?php

declare(strict_types=1);

namespace Eloquent\Phony\Stub\Exception;

use Eloquent\Phony\Assertion\AssertionRenderer;
use Eloquent\Phony\Matcher\MatcherSet;
use Exception;

/**
 * Stub criteria were specified, but never used.
 */
final class UnusedStubCriteriaException extends Exception
{
    /**
     * Construct a new unused stub criteria exception.
     *
     * @param MatcherSet        $criteria          The criteria.
     * @param AssertionRenderer $assertionRenderer The assertion renderer to use.
     */
    public function __construct(
        MatcherSet $criteria,
        AssertionRenderer $assertionRenderer
    ) {
        $this->criteria = $criteria;

        parent::__construct(
            sprintf(
                'Stub criteria %s were never used. ' .
                    'Check for incomplete stub rules.',
                var_export(
                    $assertionRenderer->renderMatcherSet($criteria),
                    true
                )
            )
        );
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
     * @var MatcherSet
     */
    private $criteria;
}
