<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2015 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Matcher\Verification;

use Eloquent\Phony\Call\Argument\ArgumentsInterface;

/**
 * The interface implemented by matcher verifiers.
 */
interface MatcherVerifierInterface
{
    /**
     * Verify that the supplied arguments match the supplied matchers.
     *
     * @param array<MatcherInterface>       $matchers  The matchers.
     * @param ArgumentsInterface|array|null $arguments The arguments.
     *
     * @return boolean True if the arguments match.
     */
    public function matches(array $matchers, $arguments);
}
