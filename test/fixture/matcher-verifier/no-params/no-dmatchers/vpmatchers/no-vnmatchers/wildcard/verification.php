<?php

$parameterNames = [];
$wildcard = $matcherFactory->wildcard(
    value: 3,
    minimumArguments: 2,
    maximumArguments: 3,
);
$matcherSets = [
    'positional' => [1, 2, $wildcard],
    'positional, ignored keys' => [2 => 1, 0 => 2, 1 => $wildcard],
];

$matchingCases = [
    'positional wildcards' => [1, 2, 3, 3],
    'positional wildcards, ignored keys' => [3 => 1, 2 => 2, 0 => 3, 1 => 3],
    'mixed wildcards' => [1, 2, 3, 'y' => 3],
    'named wildcards' => [1, 2, 'z' => 3, 'y' => 3],
];
$nonMatchingCases = [
    'empty' => [],
    'missing positional arg' => [1],
    'missing positional wildcard arg' => [1, 2, 3],
    'missing named wildcard arg' => [1, 2, 'z' => 3],
    'all non-match' => [-1, -1, -1, -1],
    'positional 0 non-match' => [-1, 2, 3, 3],
    'positional 1 non-match' => [1, -1, 3, 3],
    'extra positional arguments' => [1, 2, 3, 3, 3, 3, 3],
    'extra mixed arguments' => [1, 2, 3, 3, 3, 'z' => 3, 'y' => 3],
    'extra named arguments' => [1, 2, 'z' => 3, 'y' => 3, 'x' => 3, 'w' => 3, 'v' => 3],
    'all non-match, extra arguments' => [-1, -1, -1, -1, -1, 'z' => -1, 'y' => -1],
];
