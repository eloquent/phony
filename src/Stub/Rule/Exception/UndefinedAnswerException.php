<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2016 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Stub\Rule\Exception;

use Exception;

/**
 * No answer was defined, or the answer is incomplete.
 */
final class UndefinedAnswerException extends Exception
{
    /**
     * Construct a new undefined answer exception.
     *
     * @param Exception|null $cause The cause, if available.
     */
    public function __construct(Exception $cause = null)
    {
        parent::__construct(
            'No answer was defined, or the answer is incomplete.',
            0,
            $cause
        );
    }
}
