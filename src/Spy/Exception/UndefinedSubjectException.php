<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Spy\Exception;

use Eloquent\Phony\Spy\SpyInterface;
use Exception;

/**
 * The requested spy subject does not exist.
 */
final class UndefinedSubjectException extends Exception
{
    /**
     * Construct a new no subject exception.
     *
     * @param SpyInterface   $spy   The spy.
     * @param Exception|null $cause The cause, if available.
     */
    public function __construct(SpyInterface $spy, Exception $cause = null)
    {
        $this->spy = $spy;

        parent::__construct(
            'The requested spy subject does not exist.',
            0,
            $cause
        );
    }

    /**
     * Get the spy.
     *
     * @return SpyInterface The spy.
     */
    public function spy()
    {
        return $this->spy;
    }

    private $spy;
}
