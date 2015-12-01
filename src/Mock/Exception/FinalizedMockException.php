<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2015 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Mock\Exception;

use Exception;

/**
 * Unable to modify a finalized mock.
 */
final class FinalizedMockException extends Exception implements
    MockExceptionInterface
{
    /**
     * Construct a finalized mock exception.
     *
     * @param Exception|null $cause The cause, if available.
     */
    public function __construct(Exception $cause = null)
    {
        parent::__construct('Unable to modify a finalized mock.', 0, $cause);
    }
}
