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
 * Represents a stub answer.
 */
class Answer implements AnswerInterface
{
    /**
     * Construct a new answer.
     *
     * @param CallRequestInterface|null   $primaryRequest    The primary request.
     * @param array<CallRequestInterface> $secondaryRequests The secondary requests.
     */
    public function __construct(
        CallRequestInterface $primaryRequest = null,
        array $secondaryRequests = array()
    ) {
        $this->primaryRequest = $primaryRequest;
        $this->secondaryRequests = $secondaryRequests;
    }

    /**
     * Get the primary request.
     *
     * @return CallRequestInterface|null The primary request, or null if none has been set.
     */
    public function primaryRequest()
    {
        return $this->primaryRequest;
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
