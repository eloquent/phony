<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Difference\Renderer;

/**
 * The interface implemented by difference renderers.
 */
interface DifferenceRendererInterface
{
    /**
     * Render a line difference.
     *
     * @param array<tuple<string,string>> $difference  The difference as an array of 2-tuples of change type and content.
     * @param string|null                 $fromLabel   The 'from' side label.
     * @param string|null                 $toLabel     The 'to' side label.
     * @param integer|null                $contextSize The number of context lines to include on either side of differences.
     *
     * @return string The rendered difference.
     */
    public function renderLineDifference(
        array $difference,
        $fromLabel = null,
        $toLabel = null,
        $contextSize = null
    );
}
