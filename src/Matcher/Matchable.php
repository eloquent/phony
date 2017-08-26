<?php

declare(strict_types=1);

namespace Eloquent\Phony\Matcher;

use Eloquent\Phony\Exporter\Exporter;

/**
 * The interface implemented by both singular and wildcard matchers.
 */
interface Matchable
{
    /**
     * Describe this matcher.
     *
     * @param Exporter|null $exporter The exporter to use.
     *
     * @return string The description.
     */
    public function describe(Exporter $exporter = null): string;

    /**
     * Describe this matcher.
     *
     * @return string The description.
     */
    public function __toString(): string;
}
