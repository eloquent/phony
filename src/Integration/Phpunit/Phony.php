<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Integration\Phpunit;

use Eloquent\Phony\Assertion\AssertionRecorderInterface;
use Eloquent\Phony\Facade\AbstractFacade;
use Eloquent\Phony\Matcher\Factory\MatcherFactory;
use Eloquent\Phony\Matcher\Factory\MatcherFactoryInterface;

/**
 * A facade for Phony usage under PHPUnit.
 */
class Phony extends AbstractFacade
{
    /**
     * Get the static matcher factory.
     *
     * @internal
     *
     * @return MatcherFactoryInterface The matcher factory.
     */
    protected static function matcherFactory()
    {
        return static::service(
            'Eloquent\Phony\Matcher\Factory\MatcherFactoryInterface',
            function () {
                return
                    new MatcherFactory(array(PhpunitMatcherDriver::instance()));
            }
        );
    }

    /**
     * Get the static assertion recorder.
     *
     * @internal
     *
     * @return AssertionRecorderInterface The assertion recorder.
     */
    protected static function assertionRecorder()
    {
        return static::service(
            'Eloquent\Phony\Assertion\AssertionRecorderInterface',
            array(
                'Eloquent\Phony\Integration\Phpunit\PhpunitAssertionRecorder',
                'instance',
            )
        );
    }
}
