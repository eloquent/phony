<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Stub\Answer;

/**
 * The interface implemented by answers.
 */
interface AnswerInterface
{
    /**
     * Get the primary request.
     *
     * @return CallRequestInterface The primary request.
     */
    public function primaryRequest();

    /**
     * Add a secondary request.
     *
     * @param CallRequestInterface $secondaryRequest The secondary request.
     */
    public function addSecondaryRequest(CallRequestInterface $secondaryRequest);

    /**
     * Get the secondary requests.
     *
     * @return array<CallRequestInterface> The secondary requests.
     */
    public function secondaryRequests();
}
