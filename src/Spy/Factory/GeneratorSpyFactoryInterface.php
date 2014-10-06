<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Spy\Factory;

use Eloquent\Phony\Call\CallInterface;
use Generator;

/**
 * The interface implemented by generator spy factories.
 */
interface GeneratorSpyFactoryInterface
{
    /**
     * Create a new generator spy.
     *
     * @param CallInterface $call      The call from which the generator originated.
     * @param Generator     $generator The generator.
     *
     * @return Generator The newly created generator spy.
     */
    public function create(CallInterface $call, Generator $generator);
}
