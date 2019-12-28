<?php

declare(strict_types=1);

namespace Eloquent\Phony\Exporter;

/**
 * The interface implemented by exporters.
 */
interface Exporter
{
    /**
     * Set the default depth.
     *
     * Negative depths are treated as infinite depth.
     *
     * @param int $depth The depth.
     *
     * @return int The previous depth.
     */
    public function setDepth(int $depth): int;

    /**
     * Export the supplied value.
     *
     * Negative depths are treated as infinite depth.
     *
     * @param mixed $value The value.
     * @param ?int  $depth The depth, or null to use the default.
     *
     * @return string The exported value.
     */
    public function export(&$value, int $depth = null): string;

    /**
     * Export a string representation of a callable value.
     *
     * @param callable $callback The callable.
     *
     * @return string The exported callable.
     */
    public function exportCallable(callable $callback): string;
}
