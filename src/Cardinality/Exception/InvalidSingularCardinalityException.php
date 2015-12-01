<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2015 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Cardinality\Exception;

use Exception;

/**
 * The specified cardinality is invalid for events that can only happen once or
 * not at all.
 */
final class InvalidSingularCardinalityException extends Exception implements
    InvalidCardinalityExceptionInterface
{
    /**
     * Construct a new invalid singular cardinality exception.
     *
     * @param tuple<integer,integer|null> $cardinality The cardinality.
     * @param Exception|null              $cause       The cause, if available.
     */
    public function __construct($cardinality, Exception $cause = null)
    {
        $this->cardinality = $cardinality;

        parent::__construct(
            'The specified cardinality is invalid for events ' .
                'that can only happen once or not at all.',
            0,
            $cause
        );
    }

    /**
     * Get the cardinality.
     *
     * @return tuple<integer,integer|null> The cardinality.
     */
    public function cardinality()
    {
        return $this->cardinality;
    }

    private $cardinality;
}
