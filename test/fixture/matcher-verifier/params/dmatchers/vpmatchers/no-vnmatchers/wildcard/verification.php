<?php

$parameterNames = ['a', 'b', 'c'];
$wildcard = $matcherFactory->wildcard(
    value: 7,
    minimumArguments: 2,
    maximumArguments: 3,
);
$matcherSets = [
    'positional' => [1, 2, 3, 4, 5, 6, $wildcard],
    'positional, ignored keys' => [2 => 1, 4 => 2, 5 => 3, 6 => 4, 1 => 5, 3 => 6, 0 => $wildcard],
];

$matchingCases = [
    'positional, minimum wildcards' => [1, 2, 3, 4, 5, 6, 7, 7],
    'positional, minimum wildcards, ignored keys' => [2 => 1, 4 => 2, 5 => 3, 6 => 4, 1 => 5, 3 => 6, 7 => 7, 0 => 7],
    'mixed, minimum wildcards' => [1, 2, 3, 4, 5, 6, 7, 'z' => 7],
    'positional, maximum wildcards' => [1, 2, 3, 4, 5, 6, 7, 7, 7],
    'positional, maximum wildcards, ignored keys' => [2 => 1, 4 => 2, 5 => 3, 6 => 4, 1 => 5, 3 => 6, 8 => 7, 0 => 7, 7 => 7],
    'mixed, maximum wildcards' => [1, 2, 3, 4, 5, 6, 7, 7, 'z' => 7],
];
$nonMatchingCases = [
    'empty' => [],

    'missing declared' => [1, 2, 'z' => 7, 'y' => 7],
    'missing variadic' => [1, 2, 3, 4, 'z' => 7, 'y' => 7],

    'positional, below minimum wildcards' => [1, 2, 3, 4, 5, 6, 7],
    'mixed, below minimum wildcards' => [1, 2, 3, 4, 5, 6, 'z' => 7],

    'positional, above minimum wildcards' => [1, 2, 3, 4, 5, 6, 7, 7, 7, 7],
    'mixed, above minimum wildcards' => [1, 2, 3, 4, 5, 6, 7, 7, 'z' => 7, 'y' => 7],

    'positional, all non-match' => [-1, -1, -1, -1, -1, -1, -1, -1],
    'mixed, all non-match' => [-1, -1, -1, -1, -1, -1, 'z' => -1, 'y' => -1],

    'positional, leading declared non-match' => [-1, 2, 3, 4, 5, 6, 7, 7],
    'mixed, leading declared non-match' => [-1, 2, 3, 4, 5, 6, 'z' => 7, 'y' => 7],

    'positional, middle declared non-match' => [1, -1, 3, 4, 5, 6, 7, 7],
    'mixed, middle declared non-match' => [1, -1, 3, 4, 5, 6, 'z' => 7, 'y' => 7],

    'positional, trailing declared non-match' => [1, 2, -1, 4, 5, 6, 7, 7],
    'mixed, trailing declared non-match' => [1, 2, -1, 4, 5, 6, 'z' => 7, 'y' => 7],

    'positional, leading variadic non-match' => [1, 2, 3, -1, 5, 6, 7, 7],
    'mixed, leading variadic non-match' => [1, 2, 3, -1, 5, 6, 'z' => 7, 'y' => 7],

    'positional, middle variadic non-match' => [1, 2, 3, 4, -1, 6, 7, 7],
    'mixed, middle variadic non-match' => [1, 2, 3, 4, -1, 6, 'z' => 7, 'y' => 7],

    'positional, trailing variadic non-match' => [1, 2, 3, 4, 5, -1, 7, 7],
    'named, trailing variadic non-match' => [1, 2, 3, 4, 5, -1, 'z' => 7, 'y' => 7],

    'positional, leading wildcard non-match' => [1, 2, 3, 4, 5, 6, -1, 7],
    'mixed, leading wildcard non-match' => [1, 2, 3, 4, 5, 6, 'z' => 7, 'y' => -1],

    'positional, trailing wildcard non-match' => [1, 2, 3, 4, 5, 6, 7, -1],
    'mixed, trailing wildcard non-match' => [1, 2, 3, 4, 5, 6, 'z' => -1, 'y' => 7],

    'extra arguments' => [1, 2, 3, 4, 5, 6, 7, 1, 2, 'z' => 7, 'y' => 1, 'x' => 2],
    'all non-match, extra arguments' => [-1, -1, -1, -1, -1, -1, -1, -1, -1, 'z' => -1, 'y' => -1, 'x' => -1],
];
