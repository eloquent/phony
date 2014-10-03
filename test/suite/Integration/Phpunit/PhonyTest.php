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

use Eloquent\Phony\Matcher\Factory\MatcherFactory;
use PHPUnit_Framework_TestCase;
use ReflectionClass;
use ReflectionProperty;

class PhonyTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $reflector = new ReflectionClass('Eloquent\Phony\Integration\Phpunit\Phony');
        foreach ($reflector->getProperties(ReflectionProperty::IS_STATIC) as $property) {
            $property->setAccessible(true);
            $property->setValue(null, null);
        }
    }

    public function testSpy()
    {
        $callback = function () {};
        $actual = Phony::spy($callback);

        $this->assertInstanceOf('Eloquent\Phony\Spy\SpyVerifier', $actual);
        $this->assertSame($callback, $actual->callback());
        $this->assertSame(array(PhpunitMatcherDriver::instance()), $actual->matcherFactory()->drivers());
        $this->assertSame(PhpunitAssertionRecorder::instance(), $actual->callVerifierFactory()->assertionRecorder());
    }

    public function testStub()
    {
        $callback = function () {};
        $actual = Phony::stub($callback);

        $this->assertInstanceOf('Eloquent\Phony\Stub\StubVerifier', $actual);
        $this->assertSame($callback, $actual->stub()->callback());
        $this->assertSame($actual->stub(), $actual->spy()->callback());
        $this->assertSame(array(PhpunitMatcherDriver::instance()), $actual->matcherFactory()->drivers());
        $this->assertSame(PhpunitAssertionRecorder::instance(), $actual->callVerifierFactory()->assertionRecorder());
    }
}
