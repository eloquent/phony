<?php

use Eloquent\Phony\Phony;
use Evenement\EventEmitterInterface;

require __DIR__ . '/../../../../vendor/autoload.php';
require __DIR__ . '/../src/EventEmitter.php';

error_reporting(-1);

return function (EventEmitterInterface $emitter) {
    $coverage = new PHP_CodeCoverage();
    $coverage->filter()->addDirectoryToWhitelist(__DIR__ . '/../src');

    $emitter->on('test.start', function ($test) use ($coverage) {
        $coverage->start($test->getTitle());
    });

    $emitter->on('test.end', function () use ($coverage) {
        $coverage->stop();
    });

    $emitter->on('runner.end', function () use ($coverage) {
        $report = new PHP_CodeCoverage_Report_HTML();
        $report->process($coverage, __DIR__ . '/coverage');
    });
};
