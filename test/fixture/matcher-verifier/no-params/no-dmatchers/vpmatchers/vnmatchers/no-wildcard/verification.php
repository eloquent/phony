<?php

$parameterNames = [];
$matcherSets = [
    'order a' => [1, 2, 'a' => 3, 'b' => 4],
    'order a, ignored positional keys' => [1 => 1, 0 => 2, 'a' => 3, 'b' => 4],
    'order b' => [1, 2, 'b' => 4, 'a' => 3],
];

$matchingCases = [
    'order a' => [1, 2, 'a' => 3, 'b' => 4],
    'order a, ignored positional keys' => [1 => 1, 0 => 2, 'a' => 3, 'b' => 4],
    'order b' => [1, 2, 'b' => 4, 'a' => 3],
];
$nonMatchingCases = [
    'empty' => [],
    'missing positional arg' => [1, 'a' => 3, 'b' => 4],
    'missing named arg a' => [1, 2, 'b' => 4],
    'missing named arg b' => [1, 2, 'a' => 3],
    'all non-match' => [-1, -1, 'a' => -1, 'b' => -1],
    'positional 0 non-match' => [-1, 2, 'a' => 3, 'b' => 4],
    'positional 1 non-match' => [1, -1, 'a' => 3, 'b' => 4],
    'named a non-match' => [1, 2, 'a' => -1, 'b' => 4],
    'named b non-match' => [1, 2, 'a' => 3, 'b' => -1],
    'extra arguments' => [1, 2, 'a' => 3, 'b' => 4, 'z' => 3, 'y' => 4],
    'all non-match, extra arguments' => [-1, -1, 'a' => -1, 'b' => -1, 'z' => -1, 'y' => -1],
];
