<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2015 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Feature;

use Eloquent\Phony\Feature\Exception\UndefinedFeatureException;

/**
 * The interface implemented by feature detectors.
 */
interface FeatureDetectorInterface
{
    /**
     * Returns true if the specified feature is supported by the current
     * runtime environment.
     *
     * @param string $feature The feature.
     *
     * @return boolean                   True if supported.
     * @throws UndefinedFeatureException If the specified feature is undefined.
     */
    public function isSupported($feature);
}
