<?php

declare(strict_types=1);

namespace Eloquent\Phony\Reflection\Exception;

use Exception;

/**
 * The specified feature is undefined.
 */
final class UndefinedFeatureException extends Exception
{
    /**
     * Construct a new undefined feature exception.
     *
     * @param string $feature The feature.
     */
    public function __construct(string $feature)
    {
        $this->feature = $feature;

        parent::__construct(
            sprintf('Undefined feature %s.', var_export($feature, true))
        );
    }

    /**
     * Get the feature.
     *
     * @return string The feature.
     */
    public function feature(): string
    {
        return $this->feature;
    }

    /**
     * @var string
     */
    private $feature;
}
