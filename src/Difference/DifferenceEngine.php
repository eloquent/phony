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

        while (($fromPair = each($from)) && $fromPair[1] !== $item) {
            $difference[] = array('-', $fromPair[1]);
        }

        while (($toPair = each($to)) && $toPair[1] !== $item) {
            $difference[] = array('+', $toPair[1]);
        }

        return $difference;
    }

    /**
     * Returns the longest common subsequence of the given sequences.
     *
     * @link http://en.wikipedia.org/wiki/Longest_common_subsequence_problem
     *
     * @param array<integer,mixed> $first The first sequence.
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

    private static $instance;
}
