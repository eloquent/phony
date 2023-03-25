<?php

$parameterNames = ['a', 'b'];
$matcherSets = [
    'empty' => [],
];

$matchingCases = [
    'empty' => [],
];
$nonMatchingCases = [
    'positional' => [-1, -1, -1, -1],
    'mixed' => [-1, 'b' => -1, 'z' => -1, 'y' => -1],
    'named' => ['a' => -1, 'b' => -1, 'z' => -1, 'y' => -1],
];
