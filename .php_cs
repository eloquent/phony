<?php

$finder = Symfony\CS\Finder\DefaultFinder::create()->in(__DIR__);

return Symfony\CS\Config\Config::create()
    ->fixers(
        array(
            '-concat_without_spaces',
            '-double_arrow_multiline_whitespaces',
            '-empty_return',
            '-new_with_braces',
            '-phpdoc_separation',
            'concat_with_spaces',
            'ordered_use',
        )
    )
    ->finder($finder);
