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
 * Represents a stub answer.
 *
 * @internal
 */
class Answer implements AnswerInterface
{
    /**
     * Construct a new answer.
     *
     * @param CallRequestInterface             $primaryRequest    The primary request.
     * @param array<CallRequestInterface>|null $secondaryRequests The secondary requests.
     */
    public function __construct(
        CallRequestInterface $primaryRequest,
        array $secondaryRequests = null
    ) {
        if (null === $secondaryRequests) {
            $secondaryRequests = array();
        }

        $this->primaryRequest = $primaryRequest;
        $this->secondaryRequests = $secondaryRequests;
    }

    /**
     * Get the primary request.
     *
     * @return CallRequestInterface The primary request.
     */
    public function primaryRequest()
    {
        return $this->primaryRequest;
    }

    /**
     * Add a secondary request.
     *
     * @param CallRequestInterface $secondaryRequest The secondary request.
     */
    public function addSecondaryRequest(CallRequestInterface $secondaryRequest)
    {
        $this->secondaryRequests[] = $secondaryRequest;
    }

    /**
     * Get the secondary requests.
     *
     * @return array<CallRequestInterface> The secondary requests.
     */
    public function secondaryRequests()
    {
        return $this->secondaryRequests;
    }

    private $primaryRequest;
    private $secondaryRequests;
}
