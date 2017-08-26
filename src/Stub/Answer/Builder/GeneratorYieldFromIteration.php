<?php

declare(strict_types=1);

namespace Eloquent\Phony\Stub\Answer\Builder;

use Eloquent\Phony\Stub\Answer\CallRequest;

/**
 * Represents an iteration of a generator that ends in a yield from.
 */
class GeneratorYieldFromIteration
{
    /**
     * Construct a new generator yield from iteration.
     *
     * @param array<CallRequest> $requests The requests.
     * @param mixed<mixed,mixed> $values   The set of keys and values to yield.
     */
    public function __construct(array $requests, $values)
    {
        $this->requests = $requests;
        $this->values = $values;
    }

    public $requests;
    public $values;
}
