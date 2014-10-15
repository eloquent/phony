<?php

if (defined('HHVM_VERSION')) {
    $this->markTestSkipped('Not supported under HHVM.');
}

return new Eloquent\Phony\Mock\Builder\MockBuilder(
    'DateTime',
    null,
    'MockGeneratorDateTime'
);
