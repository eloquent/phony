<?php

$parameterNames = [];
$wildcard = $matcherFactory->wildcard(
    value: 5,
    minimumArguments: 2,
    maximumArguments: 3,
);
$matcherSets = [
    'order a' => [1, 2, $wildcard, 'a' => 3, 'b' => 4],
    'order a, ignored positional keys' => [2 => 1, 0 => 2, 1 => $wildcard, 'a' => 3, 'b' => 4],
    'order b' => [1, 2, $wildcard, 'b' => 4, 'a' => 3],
];

$matchingCases = [
    'positional wildcards, order a' => [1, 2, 5, 5, 'a' => 3, 'b' => 4],
    'positional wildcards, order a, ignored positional keys' => [1 => 1, 0 => 2, 3 => 5, 2 => 5, 'a' => 3, 'b' => 4],
    'positional wildcards, order b' => [1, 2, 5, 5, 'b' => 4, 'a' => 3],
    'mixed wildcards, order a' => [1, 2, 5, 'a' => 3, 'b' => 4, 'z' => 5],
    'mixed wildcards, order a, ignored positional keys' => [2 => 1, 0 => 2, 1 => 5, 'a' => 3, 'b' => 4, 'z' => 5],
    'mixed wildcards, order b' => [1, 2, 5, 'b' => 4, 'a' => 3, 'z' => 5],
    'named wildcards, order a' => [1, 2, 'a' => 3, 'b' => 4, 'z' => 5, 'y' => 5],
    'named wildcards, order a, ignored positional keys' => [1 => 1, 0 => 2, 'a' => 3, 'b' => 4, 'z' => 5, 'y' => 5],
    'named wildcards, order b' => [1, 2, 'b' => 4, 'a' => 3, 'z' => 5, 'y' => 5],
];
$nonMatchingCases = [
    'empty' => [],
    'missing positional arg' => [1, 'a' => 3, 'b' => 4, 'z' => 5, 'y' => 5],
    'missing named arg a' => [1, 2, 'b' => 4, 'z' => 5, 'y' => 5],
    'missing named arg b' => [1, 2, 'a' => 3, 'z' => 5, 'y' => 5],
    'missing positional wildcard' => [1, 2, 5, 'a' => 3, 'b' => 4],
    'missing named wildcard' => [1, 2, 'a' => 3, 'b' => 4, 'z' => 5],
    'all non-match' => [-1, -1, -1, -1, 'a' => -1, 'b' => -1],
    'positional 0 non-match' => [-1, 2, 5, 5, 'a' => 3, 'b' => 4],
    'positional 1 non-match' => [1, -1, 5, 5, 'a' => 3, 'b' => 4],
    'named a non-match' => [1, 2, 5, 5, 'a' => -1, 'b' => 4],
    'named b non-match' => [1, 2, 5, 5, 'a' => 3, 'b' => -1],
    'extra arguments' => [1, 2, 5, 1, 2, 'a' => 3, 'b' => 4, 'z' => 5, 'y' => 1, 'x' => 2],
    'all non-match, extra arguments' => [-1, -1, -1, -1, -1, 'a' => -1, 'b' => -1, 'z' => -1, 'y' => -1, 'x' => -1],
];
