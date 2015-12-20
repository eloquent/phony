<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2015 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Exporter;

/**
 * The interface implemented by exporters.
 */
interface ExporterInterface
{
    /**
     * Set the default depth.
     *
     * Negative depths are treated as infinite depth.
     *
     * @param integer $depth The depth.
     *
     * @return integer The previous depth.
     */
    public function setDepth($depth);

    /**
     * Export the supplied value.
     *
     * Negative depths are treated as infinite depth.
     *
     * @param mixed        &$value The value.
     * @param integer|null $depth  The depth, or null to use the default.
     *
     * @return string The exported value.
     */
    public function export(&$value, $depth = null);
}
