<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

use Eloquent\Phony\Integration\Phpunit\PhpunitMatcherDriver;
use Eloquent\Phony\Matcher\Factory\MatcherFactory;
use Eloquent\Phony\Spy\SpyVerifier;

class FunctionalTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->matcherFactory = new MatcherFactory(array(new PhpunitMatcherDriver()));
    }

    public function testTypicalCalledWith()
    {
        $spy = new SpyVerifier(null, $this->matcherFactory);
        $spy('argumentA', 'argumentB', 'argumentC');
        $spy(111);

        $this->assertTrue($spy->calledWith('argumentA', 'argumentB', 'argumentC'));
        $this->assertTrue($spy->calledWith('argumentA', 'argumentB'));
        $this->assertTrue($spy->calledWith('argumentA'));
        $this->assertTrue($spy->calledWith());
        $this->assertTrue($spy->calledWith(111));
        $this->assertTrue($spy->calledWith($this->identicalTo('argumentA'), $this->anything()));
    }
}
