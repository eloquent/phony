<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2015 Erin Millard
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
     * Set the primary request.
     *
     * @param CallRequestInterface $primaryRequest The primary request.
     */
    public function setPrimaryRequest(CallRequestInterface $primaryRequest);

    /**
     * Get the primary request.
     *
     * @return CallRequestInterface|null The primary request, or null if none has been set.
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
