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
 * Renders line differences.
 */
class LineDifferenceRenderer implements LineDifferenceRendererInterface
{
    /**
     * Get the static instance of this renderer.
     *
     * @return LineDifferenceRendererInterface The static renderer.
     */
    public static function instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Construct a new line difference renderer.
     *
     * @param string|null  $endOfLine   The end-of-line sequence to use.
     * @param integer|null $contextSize The number of context lines to include on either side of differences.
     */
    public function __construct($endOfLine = null, $contextSize = null)
    {
        if (null === $endOfLine) {
            $endOfLine = "\n";
        }
        if (null === $contextSize) {
            $contextSize = 3;
        }

        $this->endOfLine = $endOfLine;
        $this->contextSize = $contextSize;
    }

    /**
     * Get the end-of-line sequence.
     *
     * @return string The end-of-line sequence.
     */
    public function endOfLine()
    {
        return $this->endOfLine;
    }

    /**
     * Get the context size.
     *
     * @return integer The context size.
     */
    public function contextSize()
    {
        return $this->contextSize;
    }

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
    ) {
        if (null === $contextSize) {
            $contextSize = $this->contextSize;
        }

        $fromLineNumber = 0;
        $toLineNumber = 0;
        $fromLineNumbers = array();
        $toLineNumbers = array();

        foreach ($difference as $index => $line) {
            switch ($line[0]) {
                case '-':
                    $fromLineNumber++;

                    break;

                case '+':
                    $toLineNumber++;

                    break;

                default:
                    $fromLineNumber++;
                    $toLineNumber++;
            }

            $fromLineNumbers[$index] = $fromLineNumber ?: 1;
            $toLineNumbers[$index] = $toLineNumber ?: 1;
        }

        if (null === $fromLabel) {
            $rendered = sprintf('---%s', $this->endOfLine);
        } else {
            $rendered = sprintf('--- %s%s', $fromLabel, $this->endOfLine);
        }

        if (null === $toLabel) {
            $rendered .= sprintf('+++%s', $this->endOfLine);
        } else {
            $rendered .= sprintf('+++ %s%s', $toLabel, $this->endOfLine);
        }

        $differenceSize = count($difference);

        for ($i = 0; $i < $differenceSize;) {
            $isChange = false;
            $lookahead = $i + $contextSize + 1;

            for ($j = $i; $j < $lookahead; $j++) {
                if (isset($difference[$j])) {
                    if (' ' !== $difference[$j][0]) {
                        $isChange = true;

                        break;
                    }
                } else {
                    break;
                }
            }

            if ($isChange) {
                $unchangedCount = 0;
                $end = null;

                for ($j = $i; $j < $differenceSize; $j++) {
                    if (' ' === $difference[$j][0]) {
                        $unchangedCount++;
                    } else {
                        $unchangedCount = 0;
                    }

                    if ($unchangedCount > $contextSize * 2) {
                        $end = $j - $contextSize;

                        break;
                    }
                }

                if (null === $end) {
                    $end = $differenceSize;
                }

                $rendered .= sprintf(
                    '@@ -%d,%d +%d,%d @@%s',
                    $fromLineNumbers[$i],
                    $fromLineNumbers[$end - 1] - $fromLineNumbers[$i] + 1,
                    $toLineNumbers[$i],
                    $toLineNumbers[$end - 1] - $toLineNumbers[$i] + 1,
                    $this->endOfLine
                );

                for (; $i < $end; $i++) {
                    $rendered .= $difference[$i][0] . $difference[$i][1];
                }
            } else {
                $i++;
            }
        }

        return $rendered;
    }

    private static $instance;
    private $endOfLine;
    private $contextSize;
}
