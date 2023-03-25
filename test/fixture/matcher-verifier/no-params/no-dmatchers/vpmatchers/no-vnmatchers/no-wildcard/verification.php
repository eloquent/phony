<?php

$parameterNames = [];
$matcherSets = [
    'positional' => [1, 2],
    'positional, ignored keys' => [1 => 1, 0 => 2],
];

$matchingCases = [
    'positional' => [1, 2],
    'positional, ignored keys' => [1 => 1, 0 => 2],
];
$nonMatchingCases = [
    'empty' => [],
    'missing positional arg' => [1],
    'all non-match' => [-1, -1],
    'positional 0 non-match' => [-1, 2],
    'positional 1 non-match' => [1, -1],
    'extra positional arguments' => [1, 2, 1, 2],
    'extra mixed arguments' => [1, 2, 1, 2, 'z' => 1, 'y' => 2],
    'extra named arguments' => [1, 2, 'z' => 1, 'y' => 2],
    'all non-match, extra arguments' => [-1, -1, 1, 2, 'z' => 1, 'y' => 2],
];
