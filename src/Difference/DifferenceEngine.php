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
 * Calculates the difference between two sequences.
 */
class DifferenceEngine implements DifferenceEngineInterface
{
    /**
     * Get the static instance of this engine.
     *
     * @return DifferenceEngineInterface The static engine.
     */
    public static function instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Calculate the difference between two sequences.
     *
     * @param array<integer,mixed> $from The 'from' side.
     * @param array<integer,mixed> $to   The 'to' side.
     *
     * @return array<DifferenceItemInterface> The difference.
     */
    public function difference(array $from, array $to)
    {
        $common = $this->lcs($from, $to);
        $difference = array();

        foreach ($common as $item) {
            while (($fromPair = each($from)) && $fromPair[1] !== $item) {
                $difference[] = array('-', $fromPair[1]);
            }

            while (($toPair = each($to)) && $toPair[1] !== $item) {
                $difference[] = array('+', $toPair[1]);
            }

            $difference[] = array(' ', $item);
        }

        while (($fromPair = each($from))) {
            $difference[] = array('-', $fromPair[1]);
        }

        while (($toPair = each($to))) {
            $difference[] = array('+', $toPair[1]);
        }

        return $difference;
    }

    /**
     * Calculate the difference between two strings, split by a pattern.
     *
     * @param string $pattern The pattern to use for splitting.
     * @param string $from    The 'from' side.
     * @param string $to      The 'to' side.
     *
     * @return array<DifferenceItemInterface> The difference.
     */
    public function stringDifference($pattern, $from, $to)
    {
        list($fromCombined, $fromAtoms, $fromDelimiters) =
            $this->splitByPattern($from, $pattern);
        list($toCombined, $toAtoms, $toDelimiters) =
            $this->splitByPattern($to, $pattern);

        $common = $this->lcs($fromAtoms, $toAtoms);
        $commonCount = count($common);
        $difference = array();

        foreach ($common as $index => $item) {
            while (($fromPair = each($fromAtoms)) && $fromPair[1] !== $item) {
                $difference[] = array('-', $fromCombined[$fromPair[0]]);
            }

            while (($toPair = each($toAtoms)) && $toPair[1] !== $item) {
                $difference[] = array('+', $toCombined[$toPair[0]]);
            }

            if (array_key_exists($fromPair[0], $fromDelimiters)) {
                if (!array_key_exists($toPair[0], $toDelimiters)) {
                    $isDifferent = true;
                } else {
                    $isDifferent = $commonCount - 1 === $index &&
                        $fromDelimiters[$fromPair[0]] !==
                        $toDelimiters[$toPair[0]];
                }
            } elseif (array_key_exists($toPair[0], $toDelimiters)) {
                $isDifferent = true;
            } else {
                $isDifferent = false;
            }

            if ($isDifferent) {
                $difference[] = array('-', $fromCombined[$fromPair[0]]);
                $difference[] = array('+', $toCombined[$toPair[0]]);
            } elseif ('' !== $fromCombined[$fromPair[0]]) {
                $difference[] = array(' ', $fromCombined[$fromPair[0]]);
            }
        }

        while (($fromPair = each($fromAtoms))) {
            if ('' !== $fromCombined[$fromPair[0]]) {
                $difference[] = array('-', $fromCombined[$fromPair[0]]);
            }
        }

        while (($toPair = each($toAtoms))) {
            if ('' !== $toCombined[$toPair[0]]) {
                $difference[] = array('+', $toCombined[$toPair[0]]);
            }
        }

        return $difference;
    }

    /**
     * Calculate the line difference between two strings.
     *
     * @param string $from The 'from' side.
     * @param string $to   The 'to' side.
     *
     * @return array<DifferenceItemInterface> The difference.
     */
    public function lineDifference($from, $to)
    {
        return $this->stringDifference('/(\R)/', $from, $to);
    }

    /**
     * Calculate the word difference between two strings.
     *
     * @param string $from The 'from' side.
     * @param string $to   The 'to' side.
     *
     * @return array<DifferenceItemInterface> The difference.
     */
    public function wordDifference($from, $to)
    {
        return $this->stringDifference('/([^\w]+)/', $from, $to);
    }

    /**
     * Returns the longest common subsequence of the given sequences.
     *
     * @link http://en.wikipedia.org/wiki/Longest_common_subsequence_problem
     *
     * @param array<integer,mixed> $first  The first sequence.
     * @param array<integer,mixed> $second The second sequence.
     *
     * @return array<integer,mixed> The longest common subsequence.
     */
    protected function lcs(array $first, array $second)
    {
        $m = count($first);
        $n = count($second);

        // $a[$i][$j] = length of lcs of $first[$i..$m] and $second[$j..$n]
        $a = array();

        // compute length of lcs and all subproblems
        for ($i = $m - 1; $i >= 0; $i--) {
            for ($j = $n - 1; $j >= 0; $j--) {
                if ($first[$i] === $second[$j]) {
                    $a[$i][$j] =
                        (isset($a[$i + 1][$j + 1]) ? $a[$i + 1][$j + 1] : 0) +
                        1;
                } else {
                    $a[$i][$j] = max(
                        (isset($a[$i + 1][$j]) ? $a[$i + 1][$j] : 0),
                        (isset($a[$i][$j + 1]) ? $a[$i][$j + 1] : 0)
                    );
                }
            }
        }

        // recover lcs itself
        $i = 0;
        $j = 0;
        $lcs = array();

        while ($i < $m && $j < $n) {
            if ($first[$i] === $second[$j]) {
                $lcs[] = $first[$i];

                $i++;
                $j++;
            } elseif (
                (isset($a[$i + 1][$j]) ? $a[$i + 1][$j] : 0) >=
                (isset($a[$i][$j + 1]) ? $a[$i][$j + 1] : 0)
            ) {
                $i++;
            } else {
                $j++;
            }
        }

        return $lcs;
    }

    /**
     * Split the supplied string by a pattern delimiter.
     *
     * @param string $string  The string.
     * @param string $pattern The pattern.
     *
     * @return tuple<array<string>,array<string>,array<string>> A 3-tuple of combined atoms and delimiters, atoms, and delimiters.
     */
    protected function splitByPattern($string, $pattern)
    {
        $parts = preg_split($pattern, $string, -1, PREG_SPLIT_DELIM_CAPTURE);
        $partCount = count($parts);
        $combined = array();
        $atoms = array();
        $delimiters = array();

        foreach ($parts as $index => $part) {
            if (0 === $index % 2) {
                if ('' !== $part || $index < $partCount - 1) {
                    $combined[] = $part;
                    $atoms[] = $part;
                }
            } else {
                $combined[intval($index / 2)] .= $part;
                $delimiters[] = $part;
            }
        }

        return array($combined, $atoms, $delimiters);
    }

    private static $instance;
}
