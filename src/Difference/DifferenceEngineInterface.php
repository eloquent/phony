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

use Eloquent\Phony\Comparator\ComparatorInterface;

/**
 * The interface implemented by difference engines.
 */
interface DifferenceEngineInterface
{
    /**
     * Calculate the difference between two sequences.
     *
     * @param array<integer,mixed>     $from       The 'from' side.
     * @param array<integer,mixed>     $to         The 'to' side.
     * @param ComparatorInterface|null $comparator The comparator to use when determining equality.
     *
     * @return array<DifferenceItemInterface> The difference.
     */
    public function difference(
        array $from,
        array $to,
        ComparatorInterface $comparator = null
    );

    /**
     * Calculate the difference between two strings, split by a pattern.
     *
     * @param string                   $pattern           The pattern to use for splitting.
     * @param string                   $from              The 'from' side.
     * @param string                   $to                The 'to' side.
     * @param boolean|null             $compareDelimiters True if delimiters should also be compared.
     * @param ComparatorInterface|null $comparator        The comparator to use when determining equality.
     *
     * @return array<DifferenceItemInterface> The difference.
     */
    public function stringDifference(
        $pattern,
        $from,
        $to,
        $compareDelimiters = null,
        ComparatorInterface $comparator = null
    );

    /**
     * Calculate the line difference between two strings.
     *
     * @param string                   $from              The 'from' side.
     * @param string                   $to                The 'to' side.
     * @param boolean|null             $compareDelimiters True if delimiters should also be compared.
     * @param ComparatorInterface|null $comparator        The comparator to use when determining equality.
     *
     * @return array<DifferenceItemInterface> The difference.
     */
    public function lineDifference(
        $from,
        $to,
        $compareDelimiters = null,
        ComparatorInterface $comparator = null
    );

    /**
     * Calculate the word difference between two strings.
     *
     * @param string                   $from              The 'from' side.
     * @param string                   $to                The 'to' side.
     * @param boolean|null             $compareDelimiters True if delimiters should also be compared.
     * @param ComparatorInterface|null $comparator        The comparator to use when determining equality.
     *
     * @return array<DifferenceItemInterface> The difference.
     */
    public function wordDifference(
        $from,
        $to,
        $compareDelimiters = null,
        ComparatorInterface $comparator = null
    );
}
