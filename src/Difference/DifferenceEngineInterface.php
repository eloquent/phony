<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Difference;

/**
 * The interface implemented by difference engines.
 */
interface DifferenceEngineInterface
{
    /**
     * Calculate the difference between two sequences.
     *
     * @param array<integer,mixed> $from The 'from' side.
     * @param array<integer,mixed> $to   The 'to' side.
     *
     * @return array<DifferenceItemInterface> The difference.
     */
    public function difference(array $from, array $to);

    /**
     * Calculate the line difference between two strings.
     *
     * @param string      $from       The 'from' side.
     * @param string      $to         The 'to' side.
     * @param string|null $eolPattern The pattern to use for splitting lines.
     *
     * @return array<DifferenceItemInterface> The difference.
     */
    public function lineDifference($from, $to, $eolPattern = null);
}
