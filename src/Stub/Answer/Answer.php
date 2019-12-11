<?php

declare(strict_types=1);

namespace Eloquent\Phony\Stub\Answer;

/**
 * Represents a stub answer.
 */
class Answer
{
    /**
     * Construct a new answer.
     *
     * @param CallRequest            $primaryRequest    The primary request.
     * @param array<int,CallRequest> $secondaryRequests The secondary requests.
     */
    public function __construct(
        CallRequest $primaryRequest,
        array $secondaryRequests
    ) {
        $this->primaryRequest = $primaryRequest;
        $this->secondaryRequests = $secondaryRequests;
    }

    /**
     * Get the primary request.
     *
     * @return CallRequest The primary request.
     */
    public function primaryRequest(): CallRequest
    {
        return $this->primaryRequest;
    }

    /**
     * Get the secondary requests.
     *
     * @return array<int,CallRequest> The secondary requests.
     */
    public function secondaryRequests(): array
    {
        return $this->secondaryRequests;
    }

    /**
     * @var CallRequest
     */
    private $primaryRequest;

    /**
     * @var array<int,CallRequest>
     */
    private $secondaryRequests;
}
