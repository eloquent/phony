<?php

use Evenement\EventEmitterInterface;
use Peridot\Console\Version;

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
        $name = Version::NAME;
        $version = Version::NUMBER;

        $report = new PHP_CodeCoverage_Report_HTML(
            50,
            90,
            " and <a href=\"http://peridot-php.github.io/\">$name $version</a>"
        );
        $report->process($coverage, __DIR__ . '/coverage');
    });
};
