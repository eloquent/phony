<?php

$finder = Symfony\CS\Finder\DefaultFinder::create()->in(__DIR__);

return Symfony\CS\Config\Config::create()
    ->fixers(
        array(
            '-concat_without_spaces',
            '-new_with_braces',
            'concat_with_spaces',
            'ordered_use',
        )
    )
    ->finder($finder);
