<?php

declare(strict_types=1);

namespace Eloquent\Phony\Exporter;

/**
 * A data structure used internally by exporters.
 */
final class ExporterResult
{
    /**
     * The final exporter output.
     *
     * @var string
     */
    public $final = '';

    /**
     * The primary type portion of the export output.
     *
     * @var string
     */
    public $type = '';

    /**
     * The label for values like spies that can have labels.
     *
     * @var string
     */
    public $label = '';

    /**
     * True for values that are objects.
     *
     * @var bool
     */
    public $object = false;

    /**
     * True for values that are sequential arrays.
     *
     * @var bool
     */
    public $sequence = false;

    /**
     * True for values that are associative arrays.
     *
     * @var bool
     */
    public $map = false;

    /**
     * True for values that wrap another value.
     *
     * @var bool
     */
    public $wrapper = false;

    /**
     * The result object for the value wrapped by a wrapper value.
     *
     * @var ?ExporterResult
     */
    public $child = null;

    /**
     * The result objects for each key-value pair of an object or array value.
     *
     * @var array<int,array<int,ExporterResult>>
     */
    public $children = [];
}
