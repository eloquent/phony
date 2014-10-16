<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Stub\Answer;

use PHPUnit_Framework_TestCase;

class AnswerTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->primaryRequest = new ArgumentCallRequest(1);
        $this->secondaryRequestA = new ArgumentCallRequest(2);
        $this->secondaryRequestB = new ArgumentCallRequest(3);
        $this->secondaryRequests = array($this->secondaryRequestA, $this->secondaryRequestB);
        $this->subject = new Answer($this->primaryRequest, $this->secondaryRequests);
    }

    public function testConstructor()
    {
        $this->assertSame($this->primaryRequest, $this->subject->primaryRequest());
        $this->assertSame($this->secondaryRequests, $this->subject->secondaryRequests());
    }

    public function testConstructorDefaults()
    {
        $this->subject = new Answer($this->primaryRequest);

        $this->assertSame(array(), $this->subject->secondaryRequests());
    }

    public function testAddSecondaryRequest()
    {
        $secondaryRequest = new ArgumentCallRequest(4);
        $this->subject->addSecondaryRequest($secondaryRequest);

        $this->assertSame(
            array($this->secondaryRequestA, $this->secondaryRequestB, $secondaryRequest),
            $this->subject->secondaryRequests()
        );
    }
}
