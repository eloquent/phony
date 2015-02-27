<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2015 Erin Millard
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
        $this->primaryRequest = new CallRequest('implode');
        $this->secondaryRequestA = new CallRequest('implode');
        $this->secondaryRequestB = new CallRequest('implode');
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
        $this->subject = new Answer();

        $this->assertNull($this->subject->primaryRequest());
        $this->assertSame(array(), $this->subject->secondaryRequests());
    }

    public function testSetPrimaryRequest()
    {
        $this->primaryRequest = new CallRequest('implode');
        $this->subject->setPrimaryRequest($this->primaryRequest);

        $this->assertSame($this->primaryRequest, $this->subject->primaryRequest());
    }

    public function testAddSecondaryRequest()
    {
        $request = new CallRequest('implode');
        $this->subject->addSecondaryRequest($request);

        $this->assertSame(
            array($this->secondaryRequestA, $this->secondaryRequestB, $request),
            $this->subject->secondaryRequests()
        );
    }
}
