<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Call\Event;

use Exception;

/**
 * The interface implemented by 'sent exception' events.
 */
interface SentExceptionEventInterface extends GeneratorEventInterface
{
    /**
     * Get the sent exception.
     *
     * @return Exception The sent exception.
     */
    public function exception();
}
