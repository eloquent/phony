<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

use Eloquent\Phony\Phony;

error_reporting(-1);

describe('Phony', function () {
    beforeEach(function () {
        $this->proxy = Phony::mock('Eloquent\Phony\Test\TestClassA');
        $this->mock = $this->proxy->mock();
    });

    it('should record passing mock assertions', function () {
        $this->mock->testClassAMethodA('a', 'b');

        $this->proxy->testClassAMethodA->calledWith('a', 'b');
    });

    it('should record failing mock assertions', function () {
        $this->proxy->testClassAMethodA->calledWith('a', 'b');
    });
});
