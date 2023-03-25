<?php

$parameterNames = [];
$matcherSets = [
    'empty' => [],
];

$matchingCases = [
    'empty' => [],
];
$nonMatchingCases = [
    'positional' => [-1, -1],
    'mixed' => [-1, 'z' => -1],
    'named' => ['z' => -1, 'y' => -1],
];
