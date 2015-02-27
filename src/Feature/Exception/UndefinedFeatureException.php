<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2015 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Feature\Exception;

use Exception;

/**
 * The specified feature is undefined.
 *
 * @internal
 */
final class UndefinedFeatureException extends Exception
{
    /**
     * Construct a new undefined feature exception.
     *
     * @param string         $feature The feature.
     * @param Exception|null $cause   The cause, if available.
     */
    public function __construct($feature, Exception $cause = null)
    {
        $this->feature = $feature;

        parent::__construct(
            sprintf('Undefined feature %s.', var_export($feature, true)),
            0,
            $cause
        );
    }

    /**
     * Get the feature.
     *
     * @return string The feature.
     */
    public function feature()
    {
        return $this->feature;
    }

    private $feature;
}
