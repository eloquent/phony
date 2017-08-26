<?php

declare(strict_types=1);

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2017 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Verification\Exception;

use Eloquent\Phony\Verification\Cardinality;
use Exception;

/**
 * The specified cardinality is invalid for events that can only happen once or
 * not at all.
 */
final class InvalidSingularCardinalityException extends Exception implements
    InvalidCardinalityException
{
    /**
     * Construct a new invalid singular cardinality exception.
     *
     * @param Cardinality $cardinality The cardinality.
     */
    public function __construct(Cardinality $cardinality)
    {
        $this->cardinality = $cardinality;

        parent::__construct(
            'The specified cardinality is invalid for events ' .
                'that can only happen once or not at all.'
        );
    }

    /**
     * Get the cardinality.
     *
     * @return Cardinality The cardinality.
     */
    public function cardinality(): Cardinality
    {
        return $this->cardinality;
    }

    private $cardinality;
}
