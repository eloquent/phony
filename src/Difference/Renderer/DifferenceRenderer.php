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
 * Renders differences.
 */
class DifferenceRenderer implements DifferenceRendererInterface
{
    /**
     * Get the static instance of this renderer.
     *
     * @return DifferenceRendererInterface The static renderer.
     */
    public static function instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Construct a new difference renderer.
     *
     * @param string|null $endOfLine The end-of-line sequence to use.
     * @param integer|null                $contextSize The number of context lines to include on either side of differences.
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
        foreach ($difference as $index => $line) {

            $rendered .= $line[0] . $line[1];
        }

        return $rendered;
    }

    private static $instance;
    private $endOfLine;
    private $contextSize;
}
