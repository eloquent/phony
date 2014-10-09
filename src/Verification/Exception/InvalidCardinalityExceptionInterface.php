<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Verification\Exception;

/**
 * The interface implemented by invalid cardinality exceptions.
 */
interface InvalidCardinalityExceptionInterface
{
    /**
     * Get the cardinality.
     *
     * @return tuple<integer|null,integer|null> The cardinality.
     */
    public function cardinality();
}
