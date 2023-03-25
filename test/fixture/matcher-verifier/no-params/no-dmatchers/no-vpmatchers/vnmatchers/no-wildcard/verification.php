<?php

$parameterNames = [];
$matcherSets = [
    'order a' => ['a' => 1, 'b' => 2],
    'order b' => ['b' => 2, 'a' => 1],
];

$matchingCases = [
    'order a' => ['a' => 1, 'b' => 2],
    'order b' => ['b' => 2, 'a' => 1],
];
$nonMatchingCases = [
    'empty' => [],
    'missing named arg a' => ['b' => 2],
    'missing named arg b' => ['a' => 1],
    'all non-match' => ['a' => -1, 'b' => -1],
    'named a non-match' => ['a' => -1, 'b' => 2],
    'named b non-match' => ['a' => 1, 'b' => -1],
    'extra positional arguments' => [1, 2, 'a' => 1, 'b' => 2],
    'extra mixed arguments' => [1, 2, 'a' => 1, 'b' => 2, 'z' => 1, 'y' => 2],
    'extra named arguments' => ['a' => 1, 'b' => 2, 'z' => 1, 'y' => 2],
    'all non-match, extra arguments' =>  [1, 2, 'a' => -1, 'b' => -1, 'z' => 1, 'y' => 2],
];
