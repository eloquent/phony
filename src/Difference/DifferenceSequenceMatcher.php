<?php

declare(strict_types=1);

namespace Eloquent\Phony\Difference;

/**
 * Sequence matcher source code originally taken from the php-diff package.
 *
 * Copyright (c) 2009 Chris Boulton <chris.boulton@interspire.com>
 *
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 *  - Redistributions of source code must retain the above copyright notice,
 *    this list of conditions and the following disclaimer.
 *  - Redistributions in binary form must reproduce the above copyright notice,
 *    this list of conditions and the following disclaimer in the documentation
 *    and/or other materials provided with the distribution.
 *  - Neither the name of the Chris Boulton nor the names of its contributors
 *    may be used to endorse or promote products derived from this software
 *    without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @link http://github.com/chrisboulton/php-diff
 *
 * @codeCoverageIgnore
 */
class DifferenceSequenceMatcher
{
    /**
     * Construct a new sequence matcher.
     *
     * @param array<int,mixed> $a An array containing the sequence to compare against.
     * @param array<int,mixed> $b An array containing the sequence to compare.
     */
    public function __construct(array $a, array $b)
    {
        $this->a = $a;
        $this->b = $b;
        $this->chainB();
    }

    /**
     * Return a list of all of the opcodes for the differences between the
     * two strings.
     *
     * The nested array returned contains an array describing the opcode
     * which includes:
     * 0 - The type of tag (as described below) for the opcode.
     * 1 - The beginning line in the first sequence.
     * 2 - The end line in the first sequence.
     * 3 - The beginning line in the second sequence.
     * 4 - The end line in the second sequence.
     *
     * The different types of tags include:
     * replace - The string from $i1 to $i2 in $a should be replaced by
     *           the string in $b from $j1 to $j2.
     * delete -  The string in $a from $i1 to $j2 should be deleted.
     * insert -  The string in $b from $j1 to $j2 should be inserted at
     *           $i1 in $a.
     * equal  -  The two strings with the specified ranges are equal.
     *
     * @return array<int,array<int,mixed>> Array of the opcodes describing the differences between the strings.
     */
    public function getOpCodes(): array
    {
        $i = 0;
        $j = 0;
        $opCodes = [];

        $blocks = $this->getMatchingBlocks();

        foreach ($blocks as $block) {
            list($ai, $bj, $size) = $block;

            $tag = '';

            if ($i < $ai && $j < $bj) {
                $tag = 'replace';
            } elseif ($i < $ai) {
                $tag = 'delete';
            } elseif ($j < $bj) {
                $tag = 'insert';
            }

            if ($tag) {
                $opCodes[] = [$tag, $i, $ai, $j, $bj];
            }

            $i = $ai + $size;
            $j = $bj + $size;

            if ($size) {
                $opCodes[] = ['equal', $ai, $i, $bj, $j];
            }
        }

        return $opCodes;
    }

    /**
     * Generate the internal arrays containing the list of junk and non-junk
     * characters for the second ($b) sequence.
     */
    private function chainB(): void
    {
        $length = count($this->b);
        $this->b2j = [];
        $popularDict = [];

        for ($i = 0; $i < $length; ++$i) {
            $char = $this->b[$i];

            if (isset($this->b2j[$char])) {
                if ($length >= 200 && count($this->b2j[$char]) * 100 > $length) {
                    $popularDict[$char] = 1;
                    unset($this->b2j[$char]);
                } else {
                    $this->b2j[$char][] = $i;
                }
            } else {
                $this->b2j[$char] = [$i];
            }
        }

        // Remove leftovers
        foreach (array_keys($popularDict) as $char) {
            unset($this->b2j[$char]);
        }
    }

    /**
     * Find the longest matching block in the two sequences, as defined by the
     * lower and upper constraints for each sequence. (for the first sequence,
     * $alo - $ahi and for the second sequence, $blo - $bhi).
     *
     * Essentially, of all of the maximal matching blocks, return the one that
     * startest earliest in $a, and all of those maximal matching blocks that
     * start earliest in $a, return the one that starts earliest in $b.
     *
     * If the junk callback is defined, do the above but with the restriction
     * that the junk element appears in the block. Extend it as far as possible
     * by matching only junk elements in both $a and $b.
     *
     * @param int $alo The lower constraint for the first sequence.
     * @param int $ahi The upper constraint for the first sequence.
     * @param int $blo The lower constraint for the second sequence.
     * @param int $bhi The upper constraint for the second sequence.
     *
     * @return array<int,int> Array containing the longest match that includes the starting position in $a, start in $b and the length/size.
     */
    private function findLongestMatch($alo, $ahi, $blo, $bhi)
    {
        $a = $this->a;

        $bestI = $alo;
        $bestJ = $blo;
        $bestSize = 0;

        $j2Len = [];
        $nothing = [];

        for ($i = $alo; $i < $ahi; ++$i) {
            $newJ2Len = [];

            if (isset($this->b2j[$a[$i]])) {
                $jDict = $this->b2j[$a[$i]];
            } else {
                $jDict = $nothing;
            }

            foreach ($jDict as $j) {
                if ($j < $blo) {
                    continue;
                }

                if ($j >= $bhi) {
                    break;
                }

                if (isset($j2Len[$j - 1])) {
                    $k = $j2Len[$j - 1] + 1;
                } else {
                    $k = 1;
                }

                $newJ2Len[$j] = $k;

                if ($k > $bestSize) {
                    $bestI = $i - $k + 1;
                    $bestJ = $j - $k + 1;
                    $bestSize = $k;
                }
            }

            $j2Len = $newJ2Len;
        }

        while (
            $bestI > $alo && $bestJ > $blo &&
            $this->a[$bestI - 1] === $this->b[$bestJ - 1]
        ) {
            --$bestI;
            --$bestJ;
            ++$bestSize;
        }

        while (
            $bestI + $bestSize < $ahi && ($bestJ + $bestSize) < $bhi &&
            $this->a[$bestI + $bestSize] === $this->b[$bestJ + $bestSize]
        ) {
            ++$bestSize;
        }

        while (
            $bestI > $alo && $bestJ > $blo &&
            $this->a[$bestI - 1] === $this->b[$bestJ - 1]
        ) {
            --$bestI;
            --$bestJ;
            ++$bestSize;
        }

        while (
            $bestI + $bestSize < $ahi && $bestJ + $bestSize < $bhi &&
            $this->a[$bestI + $bestSize] === $this->b[$bestJ + $bestSize]
        ) {
            ++$bestSize;
        }

        return [$bestI, $bestJ, $bestSize];
    }

    /**
     * Return a nested set of arrays for all of the matching sub-sequences
     * in the strings $a and $b.
     *
     * Each block contains the lower constraint of the block in $a, the lower
     * constraint of the block in $b and finally the number of lines that the
     * block continues for.
     *
     * @return array<int,array<int,int>> Nested array of the matching blocks, as described by the function.
     */
    private function getMatchingBlocks()
    {
        $aLength = count($this->a);
        $bLength = count($this->b);

        $queue = [[0, $aLength, 0, $bLength]];

        $matchingBlocks = [];

        while (!empty($queue)) {
            list($alo, $ahi, $blo, $bhi) = array_pop($queue);

            $x = $this->findLongestMatch($alo, $ahi, $blo, $bhi);
            list($i, $j, $k) = $x;

            if ($k) {
                $matchingBlocks[] = $x;

                if ($alo < $i && $blo < $j) {
                    $queue[] = [$alo, $i, $blo, $j];
                }

                if ($i + $k < $ahi && $j + $k < $bhi) {
                    $queue[] = [$i + $k, $ahi, $j + $k, $bhi];
                }
            }
        }

        usort($matchingBlocks, [$this, 'tupleSort']);

        $i1 = 0;
        $j1 = 0;
        $k1 = 0;
        $nonAdjacent = [];

        foreach ($matchingBlocks as $block) {
            list($i2, $j2, $k2) = $block;

            if ($i1 + $k1 === $i2 && $j1 + $k1 === $j2) {
                $k1 += $k2;
            } else {
                if ($k1) {
                    $nonAdjacent[] = [$i1, $j1, $k1];
                }

                $i1 = $i2;
                $j1 = $j2;
                $k1 = $k2;
            }
        }

        if ($k1) {
            $nonAdjacent[] = [$i1, $j1, $k1];
        }

        $nonAdjacent[] = [$aLength, $bLength, 0];

        return $nonAdjacent;
    }

    /**
     * Sort an array by the nested arrays it contains. Helper function for getMatchingBlocks.
     *
     * @param array<int,mixed> $a First array to compare.
     * @param array<int,mixed> $b Second array to compare.
     *
     * @return int -1, 0 or 1, as expected by the usort function.
     */
    private function tupleSort($a, $b)
    {
        $aLength = count($a);
        $bLength = count($b);

        if ($aLength > $bLength) {
            $max = $aLength;
        } else {
            $max = $bLength;
        }

        for ($i = 0; $i < $max; ++$i) {
            if ($a[$i] < $b[$i]) {
                return -1;
            }

            if ($a[$i] > $b[$i]) {
                return 1;
            }
        }

        return $aLength <=> $bLength;
    }

    /**
     * @var array<int,mixed>
     */
    private $a;

    /**
     * @var array<int,mixed>
     */
    private $b;

    /**
     * @var array<string,array<int,int>>
     */
    private $b2j;
}
