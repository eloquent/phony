<?php

use Eloquent\Phony\Pho\Phony;

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
