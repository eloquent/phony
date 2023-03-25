<?php

$parameterNames = ['a', 'b', 'c'];
$wildcard = $matcherFactory->wildcard(
    value: 9,
    minimumArguments: 2,
    maximumArguments: 3,
);
$matcherSets = [
    'order a' => [1, 2, 3, 4, 5, 6, $wildcard, 'd' => 7, 'e' => 8],
    'order a, ignored keys' => [2 => 1, 4 => 2, 6 => 3, 0 => 4, 1 => 5, 3 => 6, 5 => $wildcard, 'd' => 7, 'e' => 8],
    'order b' => [1, 2, 3, 4, 5, 6, $wildcard, 'e' => 8, 'd' => 7],
];

$matchingCases = [
    'positional wildcards, order a' => [1, 2, 3, 4, 5, 6, 9, 9, 'd' => 7, 'e' => 8],
    'positional wildcards, order a, ignored keys' => [7 => 1, 4 => 2, 6 => 3, 0 => 4, 1 => 5, 3 => 6, 5 => 9, 2 => 9, 'd' => 7, 'e' => 8],
    'positional wildcards, order b' => [1, 2, 3, 4, 5, 6, 9, 9, 'e' => 8, 'd' => 7],
    'mixed wildcards, order a' => [1, 2, 3, 4, 5, 6, 9, 'd' => 7, 'e' => 8, 'z' => 9],
    'mixed wildcards, order b' => [1, 2, 3, 4, 5, 6, 9, 'z' => 9, 'e' => 8, 'd' => 7],
    'named wildcards, order a' => [1, 2, 3, 4, 5, 6, 'd' => 7, 'e' => 8, 'z' => 9, 'y' => 9],
    'named wildcards, order b' => [1, 2, 3, 4, 5, 6, 'y' => 9, 'z' => 9, 'e' => 8, 'd' => 7],
];
$nonMatchingCases = [
    'empty' => [],
    'missing declared arg' => [1, 2, 'd' => 4, 'e' => 5, 'z' => 9, 'y' => 9],
    'missing variadic positional arg' => [1, 2, 3, 4, 5, 'd' => 7, 'e' => 8, 'z' => 9, 'y' => 9],
    'missing variadic named arg' => [1, 2, 3, 4, 5, 6, 9, 9, 'e' => 8],
    'missing positional wildcard' => [1, 2, 3, 4, 5, 6, 9, 'd' => 7, 'e' => 8],
    'missing named wildcard' => [1, 2, 3, 4, 5, 6, 'd' => 7, 'e' => 8, 'z' => 9],
    'all non-match' => [-1, -1, -1, -1, -1, -1, -1, -1, 'd' => -1, 'e' => -1],
    'leading declared non-match' => [-1, 2, 3, 4, 5, 6, 9, 9, 'd' => 7, 'e' => 8],
    'middle declared non-match' => [1, -1, 3, 4, 5, 6, 9, 9, 'd' => 7, 'e' => 8],
    'trailing declared non-match' => [1, 2, -1, 4, 5, 6, 9, 9, 'd' => 7, 'e' => 8],
    'leading variadic positional non-match' => [1, 2, 3, -1, 5, 6, 9, 9, 'd' => 7, 'e' => 8],
    'middle variadic positional non-match' => [1, 2, 3, 4, -1, 6, 9, 9, 'd' => 7, 'e' => 8],
    'trailing variadic positional non-match' => [1, 2, 3, 4, 5, -1, 9, 9, 'd' => 7, 'e' => 8],
    'variadic named d non-match' => [1, 2, 3, 4, 5, 6, 9, 9, 'd' => -1, 'e' => 8],
    'variadic named e non-match' => [1, 2, 3, 4, 5, 6, 9, 9, 'd' => 7, 'e' => -1],
    'leading positional wildcard non-match' => [1, 2, 3, 4, 5, 6, -1, 9, 'd' => 7, 'e' => 8],
    'trailing positional wildcard non-match' => [1, 2, 3, 4, 5, 6, 9, -1, 'd' => 7, 'e' => 8],
    'named wildcard z non-match' => [1, 2, 3, 4, 5, 6, 'd' => 7, 'e' => 8, 'z' => -1, 'y' => 9],
    'named wildcard y non-match' => [1, 2, 3, 4, 5, 6, 'd' => 7, 'e' => 8, 'z' => 9, 'y' => -1],
    'extra arguments' => [1, 2, 3, 4, 5, 6, 1, 2, 'd' => 7, 'e' => 8, 'z' => 9, 'y' => 9, 'x' => 9, 'w' => 9, 'v' => 9],
    'all non-match, extra arguments' => [-1, -1, -1, -1, -1, -1, -1, -1, 'd' => -1, 'e' => -1, 'z' => -1, 'y' => -1, 'x' => -1, 'w' => -1, 'v' => -1],
];
