<?php

use Evenement\EventEmitterInterface;

return function (EventEmitterInterface $emitter) {
    $emitter->on('peridot.start', function ($environment) {
        $environment->getDefinition()->getArgument('path')
            ->setDefault('FileWriter.spec.php');
    });
};
