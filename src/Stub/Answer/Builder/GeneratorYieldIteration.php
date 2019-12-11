<?php

declare(strict_types=1);

namespace Eloquent\Phony\Stub\Answer\Builder;

use Eloquent\Phony\Stub\Answer\CallRequest;

/**
 * Represents an iteration of a generator that ends in a yield.
 */
class GeneratorYieldIteration
{
    /**
     * Construct a new generator yield iteration.
     *
     * @param array<int,CallRequest> $requests The requests.
     * @param bool                   $hasKey   True if the key should be yielded.
     * @param mixed                  $key      The key.
     * @param bool                   $hasValue True if the value should be yielded.
     * @param mixed                  $value    The value.
     */
    public function __construct(
        array $requests,
        bool $hasKey,
        $key,
        bool $hasValue,
        $value
    ) {
        $this->requests = $requests;
        $this->hasKey = $hasKey;
        $this->key = $key;
        $this->hasValue = $hasValue;
        $this->value = $value;
    }

    /**
     * @var array<int,CallRequest>
     */
    public $requests;

    /**
     * @var bool
     */
    public $hasKey;

    /**
     * @var mixed
     */
    public $key;

    /**
     * @var bool
     */
    public $hasValue;

    /**
     * @var mixed
     */
    public $value;
}
