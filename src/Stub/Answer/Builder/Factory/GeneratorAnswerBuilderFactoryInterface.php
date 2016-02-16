<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2016 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Stub\Answer\Builder\Factory;

use Eloquent\Phony\Stub\Answer\Builder\GeneratorAnswerBuilderInterface;
use Eloquent\Phony\Stub\StubInterface;

/**
 * The interface implemented by generator answer builders.
 */
interface GeneratorAnswerBuilderFactoryInterface
{
    /**
     * Create a generator answer builder for the supplied stub.
     *
     * @param StubInterface $stub   The stub.
     * @param array         $values An array of keys and values to yield.
     *
     * @return GeneratorAnswerBuilderInterface The newly created builder.
     */
    public function create(StubInterface $stub, array $values = array());
}
